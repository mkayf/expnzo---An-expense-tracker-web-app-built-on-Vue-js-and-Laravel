<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BudgetController extends Controller
{
    public function __construct(protected BudgetService $budgetService)
    {
    }

    public function show(Request $request)
    {

    }

    public function setBudget(Request $request)
    {
        try {   
            $validated = $request->validate([
                'limit_amount' => ['required', 'numeric', 'min:1', 'max:999999999999']
            ]);

            $budget = $this->budgetService->setBudget($request->user(), $validated['limit_amount']);

            if ($budget) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget set successfully',
                ], 200);
            }

        } catch (\Throwable $th) {
            Log::info('Error occured while setting budget', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while setting budget, please try again later'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'limit_amount' => ['required', 'numeric', 'min:1', 'max:999999999999']
            ]);

            $budget = $this->budgetService->updateBudget($request->user(), $id, $validated['limit_amount']);

            if ($budget) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget updated successfully'
                ], 200);
            }

        } catch (\Throwable $th) {
            Log::info('Error occured while updating budget', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating your budget, please try again later'
            ], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $budget = $this->budgetService->deleteBudget($request->user(), $id);

            if ($budget) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget deleting succesfully'
                ], 200);
            }


        } catch (\Throwable $th) {
            Log::info('Error occured while deleting budget', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting your budget, please try again later'
            ], 500);
        }
    }

}
