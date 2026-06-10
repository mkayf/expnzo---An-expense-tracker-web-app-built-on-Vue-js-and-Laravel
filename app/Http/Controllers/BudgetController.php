<?php

namespace App\Http\Controllers;

use App\Services\BudgetService;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(protected BudgetService $budgetService)
    {
    }

    public function store(Request $request){
        $request->validate([
            'limit_amount' => ['required', 'numeric', 'min:1', 'max:999,999,999,999']
        ]);        
    }

}
