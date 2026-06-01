<?php

namespace App\Services;

class TransactionService
{
    public function makeTransaction($user, $data){
        $user->transactions()->create($data);
        return true;
    }
}