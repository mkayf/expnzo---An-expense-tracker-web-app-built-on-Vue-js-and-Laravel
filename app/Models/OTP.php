<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    protected $table = 'otps';
    protected $fillable = ['code', 'is_used', 'attempts', 'expires_at', 'user_id'];
}