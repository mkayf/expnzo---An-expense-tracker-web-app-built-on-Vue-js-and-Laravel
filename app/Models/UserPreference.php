<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $table = 'user_preferences';

    protected $fillable = ['user_id', 'currency'];

    public function user(){
        return $this->belongsTo('user', 'user_id');
    }
}
