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

            if($userExist){
                Auth::login($userExist);
            } else{
                $newUser = User::updateOrCreate([
                    'email' => $user->email
                ], [
                    'name' => $user->name,
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => Carbon::now(),
                    'email_verified' => true,
                    'provider' => 'google'
                ]);
                
                Auth::login($newUser);
            }

            return redirect()->to('/dashboard', 200);
        }
        catch(\Throwable $th){
            
            Log::error('Failed to authenticate user using Google OAuth');
        }
    }
}
