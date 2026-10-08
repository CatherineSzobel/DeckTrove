<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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

    public function updatePassword(Request $request)
    {
        // Named error bags keep this form's errors apart from the delete form, which also has a password field.
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // The model's "hashed" cast hashes the password.
        $request->user()->update(['password' => $validated['password']]);

        return redirect()->route('profile.edit')->with('success', 'Password changed.');
    }

    /**
     * Deletes the account. Decks and their cards are removed by the database's cascading deletes.
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('deleteAccount', ['password' => ['required', 'current_password']]);

        $user = $request->user();
        $avatar = $user->avatar;

        Auth::logout();
        $user->delete();

        if ($avatar) {
            Storage::disk(config('filesystems.media'))->delete($avatar);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('index')->with('success', 'Your account has been deleted.');
    }
}
