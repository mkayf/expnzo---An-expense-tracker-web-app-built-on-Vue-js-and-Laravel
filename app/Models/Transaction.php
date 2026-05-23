<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'category_id', 'type', 'amount', 'note', 'transaction_date'];

    public function user(){
        $this->belongsTo(User::class, 'user_id');
    }

    public function category(){
        $this->belongsTo(Category::class, 'category_id');
    }
}
