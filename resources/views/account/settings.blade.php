{{--
    Settings (/settings): Change password and Delete account. Each form is a plain POST with its own error bag.
    Edit profile (/profile/edit) has the profile card, picture, username and About; /profile is your public profile.
--}}

<x-layout title="Settings">
    <div class="mx-auto flex max-w-3xl flex-col gap-6 px-4 py-12 sm:px-6">
        <div class="flex flex-col gap-2">
            <p class="eyebrow">Your account</p>
            <h1 class="heading-display text-[clamp(40px,5vw,56px)] text-ink">Settings</h1>
        </div>

        <div class="flex flex-col gap-6">
            <section id="change-password" aria-labelledby="change-password-title" class="account-panel">
                <h2 id="change-password-title" class="heading-display text-[32px] text-ink">Change password</h2>

                <form method="POST" action="{{ route('profile.password') }}" class="flex flex-col gap-5">
                    @csrf
                    @method('PUT')
                    <x-field name="current_password" label="Current password" type="password" bag="updatePassword" autocomplete="current-password" required />
                    <x-field name="password" id="new_password" label="New password" type="password" bag="updatePassword" autocomplete="new-password" required />
                    <x-field name="password_confirmation" id="new_password_confirmation" label="Confirm new password" type="password" bag="updatePassword" autocomplete="new-password" required />
                    <button type="submit" class="btn-primary self-start">Change password</button>
                </form>
            </section>

            <section id="delete-account" aria-labelledby="delete-account-title" class="account-panel border-danger-line">
                <div class="flex flex-col gap-2">
                    <h2 id="delete-account-title" class="heading-display text-[32px] text-danger">Delete account</h2>
                    <p class="text-[15px] text-body">This permanently deletes your account, your decks and your avatar. It cannot be undone.</p>
                </div>

                <form method="POST" action="{{ route('profile.destroy') }}" class="flex flex-col gap-5"
                    data-confirm="This deletes your account, all your decks and your avatar. It cannot be undone."
                    data-confirm-title="Delete your account?" data-confirm-button="Delete account">
                    @csrf
                    @method('DELETE')
                    <x-field name="password" id="delete_password" label="Confirm with your password" type="password" bag="deleteAccount" autocomplete="current-password" required />
                    <button type="submit" class="btn-danger self-start">Delete my account</button>
                </form>
            </section>
        </div>
    </div>
</x-layout>
