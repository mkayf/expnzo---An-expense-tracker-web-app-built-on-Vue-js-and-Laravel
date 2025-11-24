<?php

namespace App\Services;


class OTPService
{
    public function generateOTP($length){
        $min = (int) str_pad('1', $length, '0');
        $max = (int) str_pad('9', $length, '9');
        return random_int($min, $max);
    }
}