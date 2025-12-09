<?php

namespace App\Jobs;

use App\Mail\EmailOTP;
use App\Models\OTP;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOTPEmailJob implements ShouldQueue
{
    use Queueable;
    
    public $tries = 3;
    public $retryAfter = 30;

    public $email;
    public $name;
    public $otp_code;

    public function __construct($user_email, $user_name, $generatedOtp)
    {
        $this->email = $user_email;
        $this->name = $user_name;
        $this->otp_code = $generatedOtp;
    }

    public function handle(): void
    {
        Mail::to($this->email, $this->name)->send(new EmailOTP($this->otp_code));
        OTP::where('code', $this->otp_code)->update(['is_sent' => true]);
        Log::info('OTP is mailed to user successfully!');
    }

    public function failed(\Throwable $exception){
        OTP::where('code', $this->otp_code)->update(['is_sent' => false, 'error' => $exception->getMessage()]);
        Log::error('Failed to send OTP mail to user');
    }    
}
