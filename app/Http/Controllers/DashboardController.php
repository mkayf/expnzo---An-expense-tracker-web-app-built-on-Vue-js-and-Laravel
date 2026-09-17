<?php

namespace App\Http\Controllers;

use App\Services\FinancialSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(protected FinancialSummaryService $financialSummaryService)
    {

    }

    public function getSummary(Request $request)
    {
        try {
            $summary = $this->financialSummaryService->statsSummary($request->user());
            return response()->json([
                'success' => true,
                'summary' => $summary
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error occured while fetching summary stats', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while getting summary stats'
            ], 500);
        }
    }

    public function getIncomeExpense(Request $request)
    {
        $validated = $request->validate([
            'months' => ['required', 'in:3,6,12']
        ]);

        try {

            $data = $this->financialSummaryService->incomeVsExpense($request->user(), $validated['months']);

            if ($data) {
                return response()->json([
                    'success' => true,
                    'message' => 'income vs expense data fetched successfully',
                    'data' => $data
                ], 200);
            }

        } catch (\Throwable $th) {
            Log::error('Error occured while fetching income vs expense', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while getting income vs expense'
            ], 500);
        }
    }

}
