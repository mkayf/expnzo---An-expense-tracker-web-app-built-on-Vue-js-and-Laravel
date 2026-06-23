<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService) {}

    public function index(Request $request)
    {
        try {
            $transactions = $this->transactionService->listTransactions($request->user());

            if ($transactions) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transactions fetched successfully',
                    'data' => $transactions
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::error('Error occured while listing transcations', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while getting transactions, please try again later'
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $validated = $request->validated([
                'id' => ['required', 'exists:transactions,id']
            ]);

            $transaction = $this->transactionService->showTransaction($request->user, $validated['id']);

            if($transaction){
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction fetched succesfully',
                    'data' => $transaction
                ], 200);
            }

        } catch (\Throwable $th) {
            Log::error('Error occured while fetching single transcation', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while getting transaction, please try again later'
            ], 500);
        }
    }

    public function store(TransactionRequest $request)
    {
        try {
            $transaction = $this->transactionService->makeTransaction($request->user(), $request->validated());

            if ($transaction) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction created successfully'
                ], 201);
            }
        } catch (\Throwable $th) {
            Log::error('Error occured while creating transaction', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while making transaction, please try again later'
            ], 500);
        }
    }

    public function update(TransactionRequest $request)
    {
        try {
            $transaction = $this->transactionService->updateTransaction($request->user(), $request->validated());

            if ($transaction) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction updated succesfully'
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::error('Error occured while updating transcation', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating transaction, please try again later'
            ], 500);
        }
    }

    public function delete(Request $request)
    {

        $validated = $request->validate([
            'id' => ['required', 'exists:transactions,id'],
        ]);

        try {
            $transaction = $this->transactionService->deleteTransaction($request->user(), $validated['id']);

            if ($transaction) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction deleted succesfully',
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::error('Error occured while deleting transaction', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting transaction, please try again later'
            ], 500);
        }
    }
}
