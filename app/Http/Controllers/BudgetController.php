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

    public function setBudget(Request $request)
    {
        $validated = $request->validate([
            'limit_amount' => ['required', 'numeric', 'min:1', 'max:100000000']
        ]);

        try {

            $budget = $this->budgetService->setBudget($request->user(), $validated['limit_amount']);

            if ($budget) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget set successfully',
                ], 200);
            }

        } catch (\Throwable $th) {
            Log::error('Error occured while setting budget', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while setting budget, please try again'
            ], 500);
        }
    }

    /* 
        This method is used in BudgetForm component to show current budget,
        fetch last month budget (if any) and other data required for that form
    */
    public function show(Request $request)
    {
        try {
            $budgetData = $this->budgetService->getBudgetData($request->user());

            return response()->json([
                'success' => true,
                'data' => $budgetData,
                'message' => 'Budget data fetch successfully'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error occured while fetching budget', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching your budget, please try again'
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
            Log::error('Error occured while deleting budget', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting your budget, please try again'
            ], 500);
        }
    }


}
