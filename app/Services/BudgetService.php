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
        $currentBudget = (float) ($user->budgets()->where('period', $this->getCurrentPeriod())->value('limit_amount') ?? 0);

        return [
            'current_budget' => $currentBudget,
            'current_period' => $this->getCurrentPeriod()
        ];
    }

    protected function getCurrentPeriod(){
        return Carbon::now()->format('Y-m');       
    }

}