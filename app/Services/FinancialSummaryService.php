<?php

namespace App\Services;

use Carbon\Carbon;

class FinancialSummaryService
{
    public function statsSummary($user){
        $total_incomes = $user->transactions()->where('type', 'income')->sum('amount');
        $total_expenses = $user->transactions()->where('type', 'expense')->sum('amount');
        $total_balance =  $total_incomes - $total_expenses;

        $current_date = Carbon::now();

        $last_month_income = $user->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount');

        $last_month_expense = $user->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount');

        $last_month_balance = $last_month_income - $last_month_expense;

        $current_month_income = $user->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()])->sum('amount');

        $current_month_expense = $user->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()])->sum('amount');

        $current_month_balance = $current_month_income - $current_month_expense;

        return [
            'balance' => [
                'label' => 'Total Balance',
                'total_balance' => $total_balance,
                'last_month_balance' => $last_month_balance,
                'current_month_balance' => $current_month_balance,
            ]
        ];

    }
}