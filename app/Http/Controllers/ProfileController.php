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
        $decks = $user->decks()->latest()->get();
        $public = $decks->where('is_public', true)->count();
        // After a failed save the form shows what was typed, so the counter counts that.
        $aboutLength = mb_strlen((string) $request->old('about', $user->about));
        // After a failed profile save the boxes show what was ticked; an unticked box isn't sent, so it reads as off.
        // username is always sent by this form, so its presence in old input means the profile form was the one that failed
        // (a failed deck bulk action redirects back with other old input and must not clear these boxes).
        $hasOld = $request->old('username') !== null;

        return view('account.edit-profile', [
            'user' => $user,
            'stats' => [
                ['value' => $decks->count(), 'label' => Str::plural('deck', $decks->count()), 'accent' => true],
                ['value' => $public, 'label' => 'public', 'accent' => false],
                ['value' => $decks->count() - $public, 'label' => 'private', 'accent' => false],
            ],
            'aboutLength' => $aboutLength,
            'aboutCounter' => "{$aboutLength} / 500",
            'showLikedDecks' => $hasOld ? (bool) $request->old('show_liked_decks') : $user->show_liked_decks,
            'allowComments' => $hasOld ? (bool) $request->old('allow_comments') : $user->allow_comments,
            // "Your decks": newest first, as on My decks.
            'decks' => $decks,
            'deckTotal' => $decks->count(),
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

        // Checkboxes: an unticked box isn't sent, so a missing one means off.
        $user->update([
            ...$validated,
            'show_liked_decks' => $request->boolean('show_liked_decks'),
            'allow_comments' => $request->boolean('allow_comments'),
        ]);

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
