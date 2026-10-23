<?php

namespace App\Services;

use Exception;
use App\Models\User;

class TransactionService
{
    public function listTransactions(User $user, $page, $per_page){
        $offset = ($page - 1) * $per_page;
        return $user->transactions()->with(['category:id,name'])->latest('transaction_date')->latest('id')->take($per_page)->skip($offset)->get();
    }

    public function showTransaction(User $user, $id){
        $transaction = $user->transactions()->findOrFail($id);
        $transaction = [
            'id' => $transaction->id,
            'category_id' => $transaction->category_id,
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'note' => $transaction->note,
            'transaction_date' => $transaction->transaction_date,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at
        ];

        return $transaction;
    }

    public function makeTransaction(User $user, array $data){
        return $user->transactions()->create($data);
    }

    public function updateTransaction(User $user, array $data){
        return $user->transactions()->where('id', $data['id'])->update($data);   
    }

    public function deleteTransaction(User $user, $id){
        return $user->transactions()->findOrFail($id)->delete();
    }
}