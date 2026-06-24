<?php

namespace App\Services;

use Carbon\Carbon;

class FinancialSummaryService
{
    public function statsSummary($user)
    {
        // Balance calculation:
        $total_incomes = $user?->transactions()->where('type', 'income')->sum('amount') ?? 0;
        $total_expenses = $user?->transactions()->where('type', 'expense')->sum('amount') ?? 0;
        $total_balance = $total_incomes - $total_expenses;

        $current_date = Carbon::now();

        $last_month_income = $user?->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount') ?? 0;

        $last_month_expense = $user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount') ?? 0;

        $last_month_balance = $last_month_income - $last_month_expense;

        $current_month_income = $user?->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()])->sum('amount') ?? 0;

        $current_month_expense = $user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()])->sum('amount') ?? 0;


        $current_month_balance = $current_month_income - $current_month_expense;

        $balancePercentage = null;
        // Direction has 3 states:
        // new => means no last month balance which means we have to show the difference amount between current month and last month
        // neutral => means percentage is 0, no gain no loss
        // up and down
        $balanceDirection = null;

        if ($last_month_balance == 0) {
            $balanceDirection = 'new';
        } else {
            $balancePercentage = (($current_month_balance - $last_month_balance) / $last_month_balance) * 100;

            if ($balancePercentage == 0) {
                $balanceDirection = 'neutral';
            } else if ($balancePercentage > 0) {
                $balanceDirection = "up";
            } else {
                $balanceDirection = 'down';
            }
        }

        // Income calculation:
        $current_month = $current_date->copy()->format('M y');

        $incomePercentage = null;
        $incomeDirection = null;

        if ($last_month_income == 0) {
            $incomeDirection = 'new';
        } else {
            $incomePercentage = (($current_month_income - $last_month_income) / $last_month_income) * 100;

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
        ->whereBetween('transaction_date',
        [
            $current_date->copy()->startOfMonth(),
            $current_date->copy()->endOfDay()
        ])
        ->selectRaw('transaction_date, SUM(amount) as amount')
        ->groupBy('transaction_date')
        ->orderBy('transaction_date')
        ->get();
        
        $income_dates = [];
        $income_amounts = [];

        foreach($daily_income as $income){
            $income_dates[] = $income['transaction_date'];
            $income_amounts[] = $income['amount'];
        }

        return [
            'balance' => [
                'total_balance' => $total_balance,
                'last_month_balance' => $last_month_balance,
                'current_month_balance' => $current_month_balance,
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
                'current_month' => $current_month,
                'current_month_income' => $current_month_income,
                'trend' => [
                    'direction' => $incomeDirection,
                    'percentage' => $incomePercentage
                ],
                'chart_data' => [
                    'income_dates' => $income_dates,
                    'income_amounts' => $income_amounts
                ]
            ]
        ];

    }
}