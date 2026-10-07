<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('account.profile', ['user' => $request->user()]);
    }

    public function edit(Request $request)
    {
        return view('account.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:30', Rule::unique('users')->ignore($user)],
            'about' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $disk = Storage::disk(config('filesystems.media'));
        $oldAvatar = $user->avatar;

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', config('filesystems.media'));
        } else {
            unset($validated['avatar']);
        }

        $user->update($validated);

        // Only remove the old avatar once the new one is stored and saved.
        if ($oldAvatar && $user->avatar !== $oldAvatar) {
            $disk->delete($oldAvatar);
        }

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }
}
