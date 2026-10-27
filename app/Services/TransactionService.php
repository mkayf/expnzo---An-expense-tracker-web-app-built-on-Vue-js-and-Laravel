<?php

namespace App\Services;

use Exception;
use App\Models\User;

class TransactionService
{
    public function listTransactions(User $user, $page, $per_page)
    {
        $offset = ($page - 1) * $per_page;

        $query = $user->transactions();
        $total = $query->count();
        $records = $query->with(['category:id,name,type'])
            ->latest('transaction_date')
            ->latest('id')
            ->skip($offset)
            ->take($per_page)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'amount' => (float) $item->amount,
                    'note' => $item->note,
                    'transaction_date' => $item->transaction_date,
                    'category' => $item->category,
                ];
            });

        return [
            'items' => $records,
            'total' => $total,
            'per_page' => (int) $per_page,
            'current_page' => (int) $page,
            'last_page' => (int) ceil($total / $per_page)
        ];
    }

    public function showTransaction(User $user, $id)
    {
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

    public function makeTransaction(User $user, array $data)
    {
        return $user->transactions()->create($data);
    }

    public function updateTransaction(User $user, array $data)
    {
        return $user->transactions()->where('id', $data['id'])->update($data);
    }

    public function deleteTransaction(User $user, $id)
    {
        return $user->transactions()->findOrFail($id)->delete();
    }
}