<?php

namespace App\Services;

use Carbon\Carbon;

class BudgetService
{
    public function setBudget($user, $amount)
    {
        $period = Carbon::now()->format('Y-m');
        return $user->budgets()->updateOrCreate([
            'period' => $period
        ], [
            'limit_amount' => $amount
        ]);
    }

    public function deleteBudget($user, $id){
        $budget = $user->budgets()->findOrFail($id);
        return $budget->delete();
    }

}