<?php

namespace App\Services;

class FinancialSummaryService
{
    public function statsSummary($user){
        $total_incomes = $user->transactions()->where('type', 'income')->sum('amount');
        $total_expenses = $user->transactions()->where('type', 'expense')->sum('amount');
        $total_balance = $total_incomes - $total_expenses;

        return [
            [
                'label' => 'Total Balance',
                'total_balance' => $total_balance,
                
            ]
        ];

    }
}