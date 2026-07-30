<?php

namespace App\Services;

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

    public function deleteBudget($user, $id){
        $budget = $user->budgets()->findOrFail($id);
        return $budget->delete();
    }

    public function getBudgetData($user){
        $current_budget = (float) ($user?->budgets()->where('period', $this->getCurrentPeriod())->value('limit_amount') ?? 0);

        $used_budget = (float) ($user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [Carbon::now()->startOfMonth(), Carbon::now()->format('Y-m-d')])->sum('amount') ?? 0);

        $remaining_budget = $current_budget - $used_budget;

        $last_budget = (float) ($user?->budgets()->where('period', '<', $this->getCurrentPeriod())->latest()->first()->limit_amount ?? 0);

        return [
            'current_budget' => $current_budget,
            'usedBudget' => $used_budget,
            'remaining_budget' => $remaining_budget,
            'last_budget' => $last_budget
        ];
    }

    protected function getCurrentPeriod(){
        return Carbon::now()->format('Y-m');       
    }

}