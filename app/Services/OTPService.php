<?php

namespace App\Services;

use App\Mail\EmailOTP;
use App\Models\OTP;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class OTPService
{
    public function generateOTP($length)
    {
        $min = (int) str_pad('1', $length, '0');
        $max = (int) str_pad('9', $length, '9');

        return random_int($min, $max);
    }

    public function sendOTP($user)
    {
        $generatedOTP = $this->generateOTP(6);

        $OTP = new OTP;
        $OTP->code = $generatedOTP;
        $OTP->user_id = $user->id;
        $OTP->resend_allowed_at = Carbon::now()->addMinute();
        $OTP->expires_at = Carbon::now()->addMinutes(10);
        $OTP->save();

        Mail::to($user->email, $user->name)->send(new EmailOTP($generatedOTP));

        return max(0, Carbon::now()->diffInSeconds($OTP->resend_allowed_at, false));
    }

    public function isResendOTPEnabled($user)
    {
        $OTP = OTP::where('user_id', $user->id)->orderBy('id', 'desc')->first();

        // If OTP is not generated or expired then mail it to user:
        if (!$OTP || $OTP->expires_at < Carbon::now()) {
            $timeLeft = $this->sendOTP($user);
            return [
                'status' => 'otp_sent',
                'timeLeft' => $timeLeft
            ];
        }

        // Get the resend_allowed_at for the countdown timer:
        $resendAllowedAt = Carbon::parse($OTP->resend_allowed_at);
        if(Carbon::now()->lessThan($resendAllowedAt)){
            $resendTimeLeft = max(0, Carbon::now()->diffInSeconds($resendAllowedAt));
            return [
                'status' => 'wait',
                'timeLeft' => $resendTimeLeft
            ];
        }else{
            return [
                'status' => 'can_resend',
                'timeLeft' => 0
            ];
        }

    }

    public function handleResendOTP($user){

    }
}
