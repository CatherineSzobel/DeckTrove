<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * "Profile" is your public profile.
     */
    public function show(Request $request)
    {
        return redirect()->route('users.show', $request->user());
    }

    /**
     * Account things: change password, delete account.
     */
    public function settings()
    {
        return view('account.settings');
    }

    /**
     * What others see: the profile card, then picture, username and About.
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        $decks = $user->decks()->count();
        $public = $user->decks()->public()->count();
        // After a failed save the form shows what was typed, so the counter counts that.
        $aboutLength = mb_strlen((string) $request->old('about', $user->about));

        return view('account.edit-profile', [
            'user' => $user,
            'stats' => [
                ['value' => $decks, 'label' => Str::plural('deck', $decks), 'accent' => true],
                ['value' => $public, 'label' => 'public', 'accent' => false],
                ['value' => $decks - $public, 'label' => 'private', 'accent' => false],
            ],
            'aboutLength' => $aboutLength,
            'aboutCounter' => "{$aboutLength} / 500",
        ]);
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

        return redirect()->to(route('profile.edit').'#edit-profile')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        // Named error bags keep this form's errors apart from the delete form, which also has a password field.
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // The model's "hashed" cast hashes the password. The new hash signs out other devices (AuthenticateSession,
        // and their keep me logged in cookies); logoutOtherDevices renews this device's cookie so it stays logged in.
        $request->user()->update(['password' => $validated['password']]);
        Auth::logoutOtherDevices($validated['password']);

        return redirect()->to(route('settings').'#change-password')->with('success', 'Password changed.');
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
