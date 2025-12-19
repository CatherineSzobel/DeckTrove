<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        return view('account.edit', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'about' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            // Generate unique filename
            $filename = uniqid() . '.' . $request->file('avatar')->getClientOriginalExtension();

            // Move file to public/avatars
            $request->file('avatar')->move(public_path('avatars'), $filename);

            // Save path relative to public
            $user->avatar = 'avatars/' . $filename;
        }

        // Update other fields
        $user->username = $request->username;
        $user->about = $request->about;
        $user->save();

        return redirect('/profile')->with('success', 'Profile updated successfully!');
    }
}
