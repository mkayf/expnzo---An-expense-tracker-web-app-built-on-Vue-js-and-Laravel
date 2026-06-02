<?php

namespace App\Services;

use Exception;
use App\Models\User;

class TransactionService
{
    public function listTransactions(User $user){
        return $user->transactions;
    }

    public function makeTransaction(User $user, array $data){
        return $user->transactions()->create($data);
    }

    public function updateTransaction(User $user, array $data){
        return $user->transactions()->where('id', $data['id'])->update($data);   
    }
}