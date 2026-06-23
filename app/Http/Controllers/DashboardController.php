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
}
