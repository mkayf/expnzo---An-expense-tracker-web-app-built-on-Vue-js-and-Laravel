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

        $disk = config('filesystems.avatar_disk');
        $user = $request->user();

        try {
            if ($request->hasFile('avatar')) {
                $oldPath = $user->getRawOriginal('avatar');
                if ($oldPath) {
                    Storage::disk($disk)->delete($oldPath);
                }

                $path = $request->file('avatar')->store('avatars', $disk);

                $user->avatar = $path;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Profile uploaded successfully',
                    'url' => Storage::disk($disk)->url($path),
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Please upload a proper image file'
            ], 400);
        } catch (\Throwable $th) {
            Log::error('Failed to upload avatar', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile. Please try again'
            ], 500);
        }
    }

    public function deleteAvatar(Request $request)
    {
        try {
            $disk = config('filesystems.avatar_disk');
            $user = $request->user();
            $path = $user->getRawOriginal('avatar');

            if ($user->has_custom_avatar) {
                Storage::disk($disk)->delete($path);

                $user->setAttribute('avatar', null);
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Profile image deleted successfully',
                    'url' => $user->avatar
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::error('Failed to delete user avatar image', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile image. Please try again'
            ], 500);
        }
    }

    public function saveProfileDetails(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:3'],
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile details saved successfully'
        ], 200);
    }

    public function savePreferences(Request $request)
    {
        $request->validate([
            'currency' => 'required|string',
            'currency_iso' => 'required|string'
        ]);

        $user = $request->user();
        $user->preferences()->update(['currency' => $request->currency, 'currency_iso' => $request->currency_iso]);
        $user->load('preferences');

        return response()->json([
            'success' => true,
            'message' => 'Preferences saved',
            'preferences' => $user->preferences
        ], 200);
    }
}
