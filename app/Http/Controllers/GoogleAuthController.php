<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(){
        return Socialite::driver('google')->redirect();
    }

    public function callback(){
        try{
            $user = Socialite::driver('google')->user();
            $userExist = User::where('email', $user->email)->first();

            Log::info('User exist or not: ', ['user' => $userExist]);

            if($userExist){
                Auth::login($userExist);
                Log::info('User found', ['user' => $userExist]);
            } else{
                Log::info('User not found, creating a new one');
                $newUser = User::updateOrCreate([
                    'email' => $user->email
                ], [
                    'name' => $user->name,
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => Carbon::now(),
                    'email_verified' => true,
                    'provider' => 'google'
                ]);
                Log::info('New user created in db', ['user' => $newUser]);    
                Auth::login($newUser);
                Log::info('New user logged in');
            }

            return redirect()->to('/dashboard')->with(['auth_success' => 'You are logged in']);
        }
        catch(\Throwable $th){
            Log::error('Failed to authenticate user using Google OAuth', ['Exception' => $th->getMessage()]);
            return redirect()->to('/auth/login')->with(['auth_error' => 'Failed to authenticate using google please try again']);
        }
    }
}
