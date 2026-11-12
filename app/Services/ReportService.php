<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class ReportService
{
    public function __construct(protected BudgetService $budgetService)
    {
    }

    public function financialReport(User $user, int|array $filters): array
    {
        if (is_int($filters)) {
            $filters = ['months' => $filters];
        }

        $transactionType = $filters['transaction_type'] ?? 'all';
        $categoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : null;
        if (isset($filters['start_date'], $filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();
        } else {
            $months = (int) ($filters['months'] ?? 6);
            $endDate = Carbon::now()->endOfDay();
            $startDate = $endDate->copy()
                ->startOfMonth()
                ->subMonthsNoOverflow($months - 1);
        }
        $months = (int) $startDate->copy()->startOfMonth()->diffInMonths($endDate->copy()->startOfMonth()) + 1;
        $calculateSavingsRate = $transactionType === 'all' && $categoryId === null;

        $dailyTotals = $this->filteredTransactions($user, $startDate, $endDate, $transactionType, $categoryId)
            ->select('type')
            ->selectRaw('transaction_date, COUNT(*) as transaction_count, SUM(amount) as amount')
            ->groupBy('transaction_date', 'type')
            ->get();

        $monthly = [];
        $month = $startDate->copy()->startOfMonth();
        $lastMonth = $endDate->copy()->startOfMonth();

        while ($month->lte($lastMonth)) {
            $monthKey = $month->format('Y-m');
            $monthly[$monthKey] = [
                'month' => $month->format('M Y'),
                'income' => 0.0,
                'expenses' => 0.0,
                'net' => 0.0,
                'savings_rate' => null,
                'transaction_count' => 0,
            ];
            $month->addMonth();
        }

        foreach ($dailyTotals as $dailyTotal) {
            $monthKey = Carbon::parse($dailyTotal->transaction_date)->format('Y-m');
            $amount = (float) $dailyTotal->amount;

            if ($dailyTotal->type === 'income') {
                $monthly[$monthKey]['income'] += $amount;
            } else {
                $monthly[$monthKey]['expenses'] += $amount;
            }

            $monthly[$monthKey]['transaction_count'] += (int) $dailyTotal->transaction_count;
        }

        foreach ($monthly as &$monthlyReport) {
            $monthlyReport['net'] = $monthlyReport['income'] - $monthlyReport['expenses'];
            $monthlyReport['savings_rate'] = $calculateSavingsRate && $monthlyReport['income'] > 0
                ? round(($monthlyReport['net'] / $monthlyReport['income']) * 100, 2)
                : null;
        }
        unset($monthlyReport);

        $monthlyReports = array_values($monthly);
        $totalIncome = array_sum(array_column($monthlyReports, 'income'));
        $totalExpenses = array_sum(array_column($monthlyReports, 'expenses'));
        $netCashFlow = $totalIncome - $totalExpenses;

        $previousEndDate = $startDate->copy()->subDay();
        $periodDays = (int) (
            $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1
        );
        $previousStartDate = $previousEndDate->copy()->subDays($periodDays - 1);
        $previousTotals = $this->filteredTransactions(
            $user,
            $previousStartDate,
            $previousEndDate,
            $transactionType,
            $categoryId
        )
            ->select('type')
            ->selectRaw('SUM(amount) as amount')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $previousIncome = (float) ($previousTotals->get('income')->amount ?? 0);
        $previousExpenses = (float) ($previousTotals->get('expense')->amount ?? 0);
        $previousNetCashFlow = $previousIncome - $previousExpenses;

        $largestExpenses = $this->filteredTransactions(
            $user,
            $startDate,
            $endDate,
            $transactionType,
            $categoryId
        )
            ->with('category:id,name')
            ->where('type', 'expense')
            ->orderByDesc('amount')
            ->orderByDesc('transaction_date')
            ->limit(5)
            ->get(['id', 'category_id', 'amount', 'note', 'transaction_date'])
            ->map(fn ($transaction) => [
                'id' => $transaction->id,
                'transaction_date' => $transaction->transaction_date,
                'category' => $transaction->category?->name,
                'note' => $transaction->note,
                'amount' => (float) $transaction->amount,
            ])
            ->values();

        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'months' => $months,
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_cash_flow' => $netCashFlow,
                'savings_rate' => $calculateSavingsRate && $totalIncome > 0
                    ? round(($netCashFlow / $totalIncome) * 100, 2)
                    : null,
                'average_monthly_expenses' => $totalExpenses / $months,
            ],
            'period_comparison' => [
                'previous_period' => [
                    'start_date' => $previousStartDate->toDateString(),
                    'end_date' => $previousEndDate->toDateString(),
                ],
                'income' => $this->comparisonMetric($totalIncome, $previousIncome),
                'expenses' => $this->comparisonMetric($totalExpenses, $previousExpenses),
                'net_cash_flow' => [
                    'current' => $netCashFlow,
                    'previous' => $previousNetCashFlow,
                    'difference' => $netCashFlow - $previousNetCashFlow,
                    'direction' => $this->comparisonDirection($netCashFlow, $previousNetCashFlow),
                ],
            ],
            'monthly' => $monthlyReports,
            'largest_expenses' => $largestExpenses,
            'budget_performance' => $transactionType === 'income'
                ? []
                : $this->budgetService->getBudgetPerformance(
                    $user,
                    array_keys($monthly),
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                    $categoryId
                ),
        ];
    }

    private function filteredTransactions(
        User $user,
        Carbon $startDate,
        Carbon $endDate,
        string $transactionType,
        ?int $categoryId
    ) {
        return $user->transactions()
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($transactionType !== 'all', fn ($query) => $query->where('type', $transactionType))
            ->when($categoryId !== null, fn ($query) => $query->where('category_id', $categoryId));
    }

    private function comparisonMetric(float $current, float $previous): array
    {
        return [
            'current' => $current,
            'previous' => $previous,
            'difference' => $current - $previous,
            'percentage_change' => $previous == 0
                ? null
                : round((($current - $previous) / $previous) * 100, 2),
            'direction' => $this->comparisonDirection($current, $previous),
        ];
    }

    private function comparisonDirection(float $current, float $previous): string
    {
        return match (true) {
            $current > $previous => 'increase',
            $current < $previous => 'decrease',
            default => 'unchanged',
        };
    }
}