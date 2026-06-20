<?php

namespace App\Services;

use Carbon\Carbon;

class FinancialSummaryService
{
    public function statsSummary($user){
        $total_incomes = $user?->transactions()->where('type', 'income')->sum('amount') ?? 0;
        $total_expenses = $user?->transactions()->where('type', 'expense')->sum('amount') ?? 0;
        $total_balance =  $total_incomes - $total_expenses;

        $current_date = Carbon::now();

        $last_month_income = $user?->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount') ?? 0;

        $last_month_expense = $user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount') ?? 0;

        $last_month_balance = $last_month_income - $last_month_expense;

        $current_month_income = $user?->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()])->sum('amount') ?? 0;

        $current_month_expense = $user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()])->sum('amount') ?? 0;

        $current_month_balance = $current_month_income - $current_month_expense;



        $percentage = null;
        // Direction has 3 states:
        // new => means no last month balance which means we have to show the difference amount between current month and last month
        // neutral => means percentage is 0, no gain no loss
        // up and down
        $direction = null;

        if($last_month_balance == 0){
            $direction = 'new';
        } else {
            $percentage = (($current_month_balance - $last_month_balance) / $last_month_balance) * 100;
            
            if($percentage == 0){
                $direction = 'neutral';
            } else if($percentage > 0){
                $direction = "up";
            } else{
                $direction = 'down';
            }
        }

        return [
            'balance' => [
                'label' => 'Total Balance',
                'total_balance' => $total_balance,
                'last_month_balance' => $last_month_balance,
                'current_month_balance' => $current_month_balance,
                'trend' => [
                    'direction' => $direction,
                    'percentage' => $percentage
                ],
                'chart_data' => [
                    'total_incomes' => $total_incomes,
                    'total_expense' => $total_expenses
                ]
            ]
        ];

    }
}