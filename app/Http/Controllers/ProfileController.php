<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class ProfileController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $user = Auth::user();
        return view('account.edit', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'about' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle avatar
        // Handle avatar upload with logging
        if ($request->hasFile('avatar')) {

            Log::info('Avatar upload attempt', [
                'user_id' => $user->id,
                'original_name' => $request->file('avatar')->getClientOriginalName(),
                'mime' => $request->file('avatar')->getMimeType(),
                'size_kb' => round($request->file('avatar')->getSize() / 1024, 2),
            ]);

            try {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Store new avatar
                $path = $request->file('avatar')->store('avatars', 'public');

                if (!$path) {
                    Log::error('Avatar upload failed: store() returned null', [
                        'user_id' => $user->id,
                    ]);
                } else {
                    Log::info('Avatar uploaded successfully', [
                        'user_id' => $user->id,
                        'path' => $path,
                    ]);

                    $validated['avatar'] = $path;
                }
            } catch (\Throwable $e) {

                Log::error('Avatar upload exception', [
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return back()->withErrors([
                    'avatar' => 'Avatar upload failed. Please try again.',
                ]);
            }
        } else {
            Log::warning('Profile update without avatar file', [
                'user_id' => $user->id,
            ]);
        }
        
        Log::info('Updating user profile', ['validated' => $validated]);
        $user->update($validated);


        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }

    public function show()
    {
        $user = Auth::user();
        return view('account.profile', compact('user'));
    }
}
