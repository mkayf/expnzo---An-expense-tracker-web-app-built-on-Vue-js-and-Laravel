<?php

namespace App\Services;

use Carbon\Carbon;

class FinancialSummaryService
{
    public function statsSummary($user)
    {
        // Balance calculation:
        $total_incomes = (float) ($user?->transactions()->where('type', 'income')->sum('amount') ?? 0);
        $total_expenses = (float) ($user?->transactions()->where('type', 'expense')->sum('amount') ?? 0);

        $total_balance = $total_incomes - $total_expenses;

        $current_date = Carbon::now();

        $last_month_income = (float) ($user?->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount') ?? 0);

        $last_month_expense = (float) ($user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount') ?? 0);

        $last_month_balance = $last_month_income - $last_month_expense;

        $current_month_transactions = $user?->transactions()->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()->endOfMonth()])->count();

        $current_month_income = (float) ($user?->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()->endOfMonth()])->sum('amount') ?? 0);

        $current_month_expense = (float) ($user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()->endOfMonth()])->sum('amount') ?? 0);

        $current_month_balance = $current_month_income - $current_month_expense;

        $balancePercentage = null;
        $balanceDirection = null;

        if ($last_month_balance != 0) {
            $balancePercentage = round((($current_month_balance - $last_month_balance) / $last_month_balance) * 100, 2);

            if ($balancePercentage == 0) {
                $balanceDirection = 'neutral';
            } else if ($balancePercentage > 0) {
                $balanceDirection = "up";
            } else {
                $balanceDirection = 'down';
            }
        }

        // Income calculation:
        $incomePercentage = null;
        $incomeDirection = null;

        if ($last_month_income != 0) {
            $incomePercentage = round((($current_month_income - $last_month_income) / $last_month_income) * 100, 2);

            if ($incomePercentage == 0) {
                $incomeDirection = 'neutral';
            } else if ($incomePercentage > 0) {
                $incomeDirection = 'up';
            } else {
                $incomeDirection = 'down';
            }
        }

        $daily_income = $user->transactions()
            ->where('type', 'income')
            ->whereBetween(
                'transaction_date',
                [
                    $current_date->copy()->startOfMonth(),
                    $current_date->copy()->endOfMonth() 
                ]
            )
            ->selectRaw('transaction_date, SUM(amount) as amount')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get();

        $income_dates = [];
        $income_amounts = [];

        foreach ($daily_income as $income) {
            $income_dates[] = $income['transaction_date'];
            $income_amounts[] = (float) $income['amount'];
        }

        // Expense calculation:
        $expensePercentage = null;
        $expenseDirection = null;

        if ($last_month_expense != 0) {
            $expensePercentage = round((($current_month_expense - $last_month_expense) / $last_month_expense) * 100,
             2);

            if ($expensePercentage == 0) {
                $expenseDirection = 'neutral';
            } else if ($expensePercentage > 0) {
                $expenseDirection = 'up';
            } else {
                $expenseDirection = 'down';
            }
        }

        $daily_expense = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween(
                'transaction_date',
                [
                    $current_date->copy()->startOfMonth(),
                    $current_date->copy()->endOfMonth()
                ]
            )
            ->selectRaw('transaction_date, SUM(amount) as amount')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get();

        $expense_dates = [];
        $expense_amounts = [];

        foreach ($daily_expense as $expense) {
            $expense_dates[] = $expense['transaction_date'];
            $expense_amounts[] = (float) $expense['amount'];
        }

        // Budget calculation:
        $period = $current_date->copy()->format('Y-m');
        $budget = (float) ($user?->budgets()->where('period', $period)->value('limit_amount') ?? 0);
        $budgetUsePercentage = null;
        $overBudgetPercentage = null;
        $budgetDirection = null;
        $usedBudget = 0;
        $remainingBudget = 0;

        if ($budget) {
            $budgetUsePercentage = round(($current_month_expense / $budget) * 100, 2);

            if ($budgetUsePercentage == 0) {
                $budgetDirection = 'neutral';
            } else if ($budgetUsePercentage > 100) {
                $budgetDirection = 'over';
                $overBudgetPercentage = round($budgetUsePercentage - 100, 2);
            } else if ($budgetUsePercentage > 75) {
                $budgetDirection = 'up';
            } else if ($budgetUsePercentage <= 75) {
                $budgetDirection = 'down';
            }

            $usedBudget = $current_month_expense;
            $remainingBudget = $budget - $usedBudget;
        }


        return [
            'balance' => [
                'amount' => $total_balance,
                'current_month_transactions' => $current_month_transactions,
                'trend' => [
                    'direction' => $balanceDirection,
                    'percentage' => abs($balancePercentage)
                ],
                'chart_data' => [
                    'total_incomes' => $total_incomes,
                    'total_expense' => $total_expenses
                ]
            ],                  
            'income' => [
                'amount' => $current_month_income,
                'trend' => [
                    'direction' => $incomeDirection,
                    'percentage' => abs($incomePercentage)
                ],
                'chart_data' => [
                    'dates' => $income_dates,
                    'amounts' => $income_amounts
                ]
            ],
            'expense' => [
                'amount' => $current_month_expense,
                'trend' => [
                    'direction' => $expenseDirection,
                    'percentage' => abs($expensePercentage)
                ],
                'chart_data' => [
                    'dates' => $expense_dates,
                    'amounts' => $expense_amounts
                ]
            ],
            'budget' => [
                'amount' => $budget,
                'trend' => [
                    'direction' => $budgetDirection,
                    'percentage' => abs($budgetUsePercentage),
                    'over_budget_percentage' => abs($overBudgetPercentage)
                ],
                'chart_data' => [
                    'used_budget' => $usedBudget,
                    'remaining_budget' => $remainingBudget
                ]
            ]
        ];

    }

    public function incomeVsExpense($user, $months = 6){
        $start_date = Carbon::now()->subMonthsNoOverflow($months - 1)->startOfMonth();

        // Get the existing months data:
        $existing_months_data = $user?->transactions()
        ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month_key")
        ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
        ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
        ->where('transaction_date', '>=', $start_date)
        ->groupBy('month_key')
        ->get()
        ->keyBy('month_key');

        // generate months when there was no transactions:
        $data = collect(range(0, $months - 1))->map(function (int $i) use ($start_date, $existing_months_data){
            $date = $start_date->copy()->addMonths($i);
            $key = $date->format('Y-m');
            $row = $existing_months_data->get($key);

            return [
                'month' => $date->format('M'),
                'income' => (float) ($row->income ?? 0),
                'expense' => (float) ($row->expense ?? 0)
            ];
        });

        return $data;
    }
}