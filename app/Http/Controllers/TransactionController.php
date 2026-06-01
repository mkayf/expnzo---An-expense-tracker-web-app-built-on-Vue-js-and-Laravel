<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function store(TransactionRequest $request, TransactionService $transactionService){
        try{
            $transaction = $transactionService->makeTransaction($request->user(), $request->validated());

            if($transaction){
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction created successfully'
                ], 201);
            }

        } catch(\Throwable $th){
            Log::info('Error occured while creating transcation', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while making transaction, please try again later'
            ], 500);
        }
    }   
}
