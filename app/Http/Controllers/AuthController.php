<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Services\OTPService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request, OTPService $OTPService)
    {
        $credentials = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        if ($credentials->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $credentials->errors()->toArray()
            ], 422);
        }

        $validatedData = $credentials->validated();
        
        DB::beginTransaction();        
        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => $validatedData['password'],
            ]);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => "Couldn't create your account, please try again",
                ], 500);
            }

            Auth::login($user);
            
            $OTPService->sendOTP($user);

            Log::info('OTP verification email sent to user');

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Account created. Check your email to verify it.',
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create account, AuthController::register, ' . $th->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ]);

        if ($credentials->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter valid email and password',
                'errors' => $credentials->errors()->toArray()
            ], 422);
        }

        $validatedData = $credentials->validated();

        try {
            if (Auth::attempt(['email' => $validatedData['email'], 'password' => $validatedData['password']])) {
                return response()->json([
                    'success' => true,
                    'message' => 'You are logged in successfully!',
                ], 200);
            } else{
                return response()->json([
                    'success' => false,
                    'message' => 'Your email or password is incorrect'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to login, AuthController::login, ' . $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request){
        try{
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return response()->json([
                'success' => true,
                'message' => 'You are logged out successfully'
            ]);
        }
        catch(\Throwable $th){
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, AuthController::logout, ' . $th->getMessage()
            ], 500);
        }
                
    }

    public function userToBeVerified(Request $request, OTPService $OTPService){

        $validation = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        if($validation->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Unable to process your request at the moment. Please check your email and try again.',
                'errors' => $validation->errors()->toArray()
            ], 422);
        }

        $validatedData = $validation->validated();

        $user = User::where('email', $validatedData['email'] ?? Auth::user()->email)->first();   
        
        if($user->email_verified === true){
            return response()->json([
                'success' => false,
                'email_status' => 'verified',
                'message' => 'Your email is already verified'
            ], 403);
        }

        $OTPInfo = $OTPService->isResendOTPEnabled($user);
        
        if($OTPInfo['status'] === 'otp_sent'){
            return response()->json([
                'success' => true,
                'timeLeft' => $OTPInfo['timeLeft'],
                'message' => 'New OTP code is sent to your email'
            ], 200);
        }


        if($OTPInfo['status'] === 'wait'){
            return response()->json([
                'success' => true,
                'timeLeft' => $OTPInfo['timeLeft'],
                'message' => 'Enter the OTP or wait for the timer to request a new one'
            ], 200);
        }

        if($OTPInfo['status'] === 'can_resend'){
            return response()->json([
                'success' => true,
                'timeLeft' => $OTPInfo['timeLeft'],
                'message' => 'You can now request a new otp'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Internal server error'
        ], 500);
    }

    public function resendOTP(Request $request, OTPService $OTPService){
        $validation = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        if($validation->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Unable to process your request at the moment. Please check your email and try again.',
                'errors' => $validation->errors()->toArray()
            ], 422);
        }

        $validatedData = $validation->validated();

        $user = User::where('email', $validatedData['email'] ?? Auth::user()->email)->first();
        
        $OTPInfo = $OTPService->handleResendOTP($user);
        
        if($OTPInfo['status'] === 'wait'){
            return response()->json([
                'success' => false,
                'message' => 'You cannot request new otp before the timer runs out.'
            ], 429);
        }

        if($OTPInfo['status'] === 'otp_sent'){
            return response()->json([
                'success' => true,
                'timeLeft' => $OTPInfo['timeLeft'],
                'message' => 'New OTP code is sent to your email'               
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cannot send OTP due to internal server error. Please try again'
        ], 500);
    }
}