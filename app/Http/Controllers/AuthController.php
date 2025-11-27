<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\EmailOTP;
use App\Models\OTP;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PDO;
use App\Services\OTPService;
use Carbon\Carbon;
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
            
            $generatedOTP = $OTPService->generateOTP(6);

            $OTP = new OTP();
            $OTP->code = $generatedOTP;
            $OTP->user_id = $user->id;
            $OTP->expires_at = Carbon::now()->addMinutes(5);
            $OTP->save(); 
                        
            Mail::to($user->email, $user->name)->send(new EmailOTP($generatedOTP));

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

    
}