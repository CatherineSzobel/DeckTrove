<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

function shareFieldErrors(array $messages = [], string $bag = 'default'): void
{
    view()->share('errors', (new ViewErrorBag)->put($bag, new MessageBag($messages)));
}

test('a field has a label, an input with its attributes and no error', function () {
    shareFieldErrors();

    $html = Blade::render('<x-field name="username" label="Username" value="kaiba" autocomplete="username" required />');

    expect($html)->toContain('<label for="username" class="text-sm font-bold text-ink">Username</label>')
        ->toContain('id="username" name="username" type="text" value="kaiba"')
        ->toContain('autocomplete="username"')
        ->toContain('required')
        ->toContain('class="input h-[50px] w-full text-base"')
        ->not->toContain('aria-invalid')
        ->not->toContain('role="alert"');
});

test('a field with an error marks the input and shows the message under it', function () {
    shareFieldErrors(['username' => 'That name is taken.']);

    $html = Blade::render('<x-field name="username" label="Username" />');

    expect($html)->toContain('aria-invalid="true" aria-describedby="username-error"')
        ->toContain('<p id="username-error" role="alert" class="flex items-center gap-1.5 text-sm text-danger">')
        ->toContain('That name is taken.');
});

test('errors from a named bag only show in fields using that bag', function () {
    shareFieldErrors(['password' => 'Wrong password.'], 'updatePassword');

    expect(Blade::render('<x-field name="password" label="Password" type="password" bag="updatePassword" />'))
        ->toContain('Wrong password.')
        ->and(Blade::render('<x-field name="password" label="Password" type="password" />'))
        ->not->toContain('Wrong password.');
});

test('a field can have its own id', function () {
    shareFieldErrors(['password' => 'Wrong.']);

    expect(Blade::render('<x-field name="password" id="current_password" label="Current password" type="password" />'))
        ->toContain('<label for="current_password"')
        ->toContain('id="current_password" name="password"')
        ->toContain('aria-describedby="current_password-error"')
        ->toContain('<p id="current_password-error" role="alert"');
});

test('a form error works on its own, without an id', function () {
    shareFieldErrors(['avatar' => 'Too big.']);

    expect(Blade::render('<x-form-error name="avatar" />'))
        ->toContain('<p role="alert" class="flex items-center gap-1.5 text-sm text-danger">')
        ->toContain('Too big.');
});
