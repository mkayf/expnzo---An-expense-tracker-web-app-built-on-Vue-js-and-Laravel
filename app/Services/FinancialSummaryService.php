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

        $current_month_income = (float) ($user?->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()])->sum('amount') ?? 0);

        $current_month_expense = (float) ($user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()])->sum('amount') ?? 0);


        $current_month_balance = $current_month_income - $current_month_expense;

        $balancePercentage = null;
        // Direction has 3 states:
        // new => means no last month balance which means we have to show the difference amount between current month and last month
        // neutral => means percentage is 0, no gain no loss
        // up and down
        $balanceDirection = null;

        if ($last_month_balance != 0 || $last_month_balance > 0) {
            $balancePercentage = round((($current_month_balance - $last_month_balance) / $last_month_balance) * 100);

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
            $incomePercentage = round((($current_month_income - $last_month_income) / $last_month_income) * 100);

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
                    $current_date->copy()->endOfDay()
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
            $expensePercentage = round((($current_month_expense - $last_month_expense) / $last_month_expense) * 100);

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
                    $current_date->copy()->endOfDay()
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
        $budgetDirection = null;
        $usedBudget = null;
        $remainingBudget = null;

        if ($budget) {
            $budgetUsePercentage = round(($current_month_expense / $budget) * 100);

            if ($budgetUsePercentage == 0) {
                $budgetDirection = 'neutral';
            } else if ($budgetUsePercentage > 100) {
                $budgetDirection = 'over';
            } else if ($budgetUsePercentage > 70) {
                $budgetDirection = 'up';
            } else if ($budgetUsePercentage <= 70) {
                $budgetDirection = 'down';
            }

            $usedBudget = $current_month_expense;
            $remainingBudget = $budget - $usedBudget;
        }


        return [
            'balance' => [
                'amount' => $total_balance,
                'trend' => [
                    'direction' => $balanceDirection,
                    'percentage' => $balancePercentage
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
                    'percentage' => $incomePercentage
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
                    'percentage' => $expensePercentage
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
                    'percentage' => $budgetUsePercentage
                ],
                'chart_data' => [
                    'used_budget' => $usedBudget,
                    'remaining_budget' => $remainingBudget
                ]
            ]
        ];

    }
}