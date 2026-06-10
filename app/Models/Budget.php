<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['user_id', 'period', 'limit_amount'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
