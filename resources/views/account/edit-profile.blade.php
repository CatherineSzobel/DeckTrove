{{--
    Edit profile (/profile/edit): your profile card, then the picture, username and About form (a plain POST).
    Cancel goes back to your public profile. Settings (/settings) has Change password and Delete account.
--}}

<x-layout title="Edit profile">
    <div class="mx-auto flex max-w-7xl flex-wrap items-start gap-6 px-4 py-12 sm:px-6">
        <x-profile.card :user="$user" label="Your profile" class="flex-[1_1_320px] lg:sticky lg:top-6">
            <div class="flex flex-col gap-1">
                <h1 class="heading-display break-words text-[40px] text-ink">{{ $user->username }}</h1>
                <p class="text-[15px] text-muted">{{ $user->email }}</p>
            </div>

            @if ($user->about)
            <p class="text-[15px] leading-relaxed text-body">{{ $user->about }}</p>
            @else
            <p class="text-[15px] text-muted">No about text yet.</p>
            @endif

            <dl class="grid grid-cols-3 gap-2 border-t border-line pt-4">
                @foreach ($stats as $stat)
                <div class="flex flex-col-reverse gap-0.5">
                    <dt class="text-xs text-muted">{{ $stat['label'] }}</dt>
                    <dd @class(['m-0 font-mono text-[22px] font-bold', 'text-accent-ink' => $stat['accent'], 'text-ink' => ! $stat['accent']])>{{ $stat['value'] }}</dd>
                </div>
                @endforeach
            </dl>

            <a href="{{ route('users.show', $user) }}" class="btn-secondary w-full">View your public profile</a>
            <a href="{{ route('decks') }}" class="btn-secondary w-full">View my decks</a>
        </x-profile.card>

        <div class="flex min-w-0 flex-[999_1_480px] flex-col gap-6">
            <section id="edit-profile" aria-labelledby="edit-profile-title" class="account-panel">
                <div class="flex flex-col gap-2">
                    <p class="eyebrow">Your profile</p>
                    <h2 id="edit-profile-title" class="heading-display text-[40px] text-ink">Edit profile</h2>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex flex-col gap-5">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="relative h-[72px] w-[72px] shrink-0">
                            <x-avatar :user="$user" class="h-[72px] w-[72px] font-display text-[32px] font-extrabold" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="avatar-pick">
                                <input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg,image/webp" class="sr-only">
                                Change picture
                            </label>
                            <span class="text-xs text-muted">JPG, PNG or WebP, max 2 MB</span>
                            <x-form-error name="avatar" id="avatar-error" />
                        </div>
                    </div>

                    <x-field name="username" label="Username" :value="old('username', $user->username)" maxlength="30" autocomplete="username" required />

                    <div class="flex flex-col gap-2">
                        <div class="flex items-baseline justify-between gap-2">
                            <label for="about" class="text-sm font-bold text-ink">About</label>
                            <span class="font-mono text-xs text-muted">{{ $aboutCounter }}</span>
                        </div>
                        <textarea id="about" name="about" rows="4" maxlength="500" @error('about') aria-invalid="true" aria-describedby="about-error" @enderror
                            class="input w-full resize-y leading-normal">{{ old('about', $user->about) }}</textarea>
                        <x-form-error name="about" id="about-error" />
                    </div>

                    <div class="flex flex-wrap justify-end gap-3">
                        <a href="{{ route('users.show', $user) }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save changes</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-layout>
