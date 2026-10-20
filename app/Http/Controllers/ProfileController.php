<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function __construct(protected AvatarService $avatars) {}

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

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $oldAvatar = $user->avatar;

        // Checkboxes: an unticked box isn't sent, so a missing one means off.
        $user->update([
            ...$request->safe()->except('avatar'),
            'avatar' => $request->hasFile('avatar') ? $this->avatars->store($request->file('avatar')) : $oldAvatar,
            'show_liked_decks' => $request->boolean('show_liked_decks'),
            'allow_comments' => $request->boolean('allow_comments'),
        ]);

        // Only remove the old avatar once the new one is stored and saved.
        if ($user->avatar !== $oldAvatar) {
            $this->avatars->delete($oldAvatar);
        }

        return redirect()->to(route('profile.edit').'#edit-profile')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $validated = $request->validated();

        // The model's "hashed" cast hashes the password. The new hash signs out other devices (AuthenticateSession,
        // and their keep me logged in cookies); logoutOtherDevices renews this device's cookie so it stays logged in.
        $request->user()->update(['password' => $validated['password']]);
        Auth::logoutOtherDevices($validated['password']);

        return redirect()->to(route('settings').'#change-password')->with('success', 'Password changed.');
    }

    /**
     * Deletes the account. Decks and their cards are removed by the database's cascading deletes.
     */
    public function destroy(DeleteAccountRequest $request)
    {
        $user = $request->user();
        $avatar = $user->avatar;

        Auth::logout();
        $user->delete();

        $this->avatars->delete($avatar);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('index')->with('success', 'Your account has been deleted.');
    }
}
