<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // User avatar handling:
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,|max:2048',
        ]);

        $user = $request->user();
        try {
            Log::info('Trying to upload user profile');
            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $path = $request->file('avatar')->store('avatars', 'public');

                $user->avatar = $path;
                $user->save();

                Log::info('Profile uploaded successfully');

                return response()->json([
                    'success' => true,
                    'message' => 'Profile uploaded successfully',
                    'url' => asset('storage/'.$path),
                ], 200);
            }
            else{
                Log::info('Image not provided by frontend', ['file_came' => $request->avatar]);

                return response()->json([
                    'success' => false,
                    'message' => 'Please upload a proper image file'
                ], 400); 
            }
        } catch (\Throwable $th) {
            Log::info('Failed to upload avatar', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile. Please try again'
            ], 500);
        }
    }

    public function showAvatar(Request $request){
        $user = $request->user();
        if($user->avatar && Storage::disk('public')->exists($user->avatar)){
            
        }
    }
}
