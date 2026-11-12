<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class BudgetService
{
    public function setBudget($user, $amount)
    {
        return $user->budgets()->updateOrCreate([
            'period' => $this->getCurrentPeriod()
        ], [
            'limit_amount' => $amount
        ]);
    }

    public function deleteBudget($user, $id)
    {
        $budget = $user->budgets()->findOrFail($id);
        return $budget->delete();
    }

    public function getBudgetData($user)
    {
        $current_budget = (float) ($user?->budgets()->where('period', $this->getCurrentPeriod())->value('limit_amount') ?? 0);

        $used_budget = $used_budget = $current_budget != 0
            ? (float) ($user?->transactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [
                    Carbon::now()->startOfMonth()->toDateString(),
                    Carbon::now()->endOfMonth()->toDateString(),
                ])
                ->sum('amount') ?? 0)
            : 0;

        $remaining_budget = $current_budget != 0 ? $current_budget - $used_budget : 0;

        $last_budget = (float) ($user?->budgets()->where('period', '<', $this->getCurrentPeriod())->latest()->first()->limit_amount ?? 0);

        return [
            'current_budget' => $current_budget,
            'used_budget' => $used_budget,
            'remaining_budget' => $remaining_budget,
            'last_budget' => $last_budget,
        ];
    }

    public function getBudgetPerformance(
        User $user,
        array $periods,
        string $startDate,
        string $endDate,
        ?int $categoryId = null
    ): array
    {
        $budgets = $user->budgets()
            ->whereIn('period', $periods)
            ->orderBy('period')
            ->get(['period', 'limit_amount'])
            ->keyBy('period');

        if ($budgets->isEmpty()) {
            return [];
        }

        $actualSpending = array_fill_keys($periods, 0.0);
        $dailyExpenses = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->when($categoryId !== null, fn ($query) => $query->where('category_id', $categoryId))
            ->selectRaw('transaction_date, SUM(amount) as amount')
            ->groupBy('transaction_date')
            ->get();

        foreach ($dailyExpenses as $dailyExpense) {
            $period = Carbon::parse($dailyExpense->transaction_date)->format('Y-m');
            if (array_key_exists($period, $actualSpending)) {
                $actualSpending[$period] += (float) $dailyExpense->amount;
            }
        }

        return $budgets->map(function ($budget, $period) use ($actualSpending) {
            $limit = (float) $budget->limit_amount;
            $actual = $actualSpending[$period];

            return [
                'period' => $period,
                'budget_limit' => $limit,
                'actual_spending' => $actual,
                'remaining_amount' => $limit - $actual,
                'status' => $actual > $limit ? 'exceeded' : 'within_budget',
            ];
        })->values()->all();
    }

    protected function getCurrentPeriod()
    {
        return Carbon::now()->format('Y-m');
    }

}