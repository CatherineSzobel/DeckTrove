<?php

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Without a previous page the redirect falls back to the profile's comments section.
function profileCommentsUrl(User $profile): string
{
    return route('users.show', $profile).'#comments';
}

test('guests are sent to log in', function () {
    $profile = User::factory()->create();

    $this->post(route('users.comments.store', $profile), ['body' => 'Hi'])->assertRedirect(route('login'));
    expect(Comment::count())->toBe(0);
});

test('a player can leave a comment on a profile', function () {
    $profile = User::factory()->create();
    $author = User::factory()->create();

    $this->actingAs($author)->post(route('users.comments.store', $profile), ['body' => 'Great decks!'])
        ->assertRedirect(profileCommentsUrl($profile))
        ->assertSessionHas('success', 'Comment posted.');

    $this->assertDatabaseHas('comments', [
        'user_id' => $author->id,
        'commentable_type' => 'user',
        'commentable_id' => $profile->id,
        'body' => 'Great decks!',
    ]);
});

test('the owner can comment on their own profile', function () {
    $profile = User::factory()->create();

    $this->actingAs($profile)->post(route('users.comments.store', $profile), ['body' => 'Thanks!'])
        ->assertRedirect(profileCommentsUrl($profile));

    expect($profile->profileComments()->count())->toBe(1);
});

test('a comment can be 500 characters, not 501', function () {
    $profile = User::factory()->create();
    $author = User::factory()->create();

    $this->actingAs($author)->post(route('users.comments.store', $profile), ['body' => str_repeat('a', 500)])
        ->assertSessionHasNoErrors();
    $this->actingAs($author)->post(route('users.comments.store', $profile), ['body' => str_repeat('a', 501)])
        ->assertSessionHasErrorsIn('comment', 'body');

    expect(Comment::count())->toBe(1);
});

test('empty, whitespace-only and array comments are refused', function () {
    $profile = User::factory()->create();
    $author = User::factory()->create();

    foreach (['', "   \n  ", ['x']] as $body) {
        $this->actingAs($author)->post(route('users.comments.store', $profile), ['body' => $body])
            ->assertSessionHasErrorsIn('comment', 'body');
    }

    expect(Comment::count())->toBe(0);
});

test('windows line breaks are stored as one character', function () {
    $profile = User::factory()->create();
    $body = str_repeat("abcd\r\n", 90).'end'; // 543 characters with \r\n, 453 with \n

    $this->actingAs(User::factory()->create())->post(route('users.comments.store', $profile), ['body' => $body])
        ->assertSessionHasNoErrors();

    expect(Comment::first()->body)->not->toContain("\r")
        ->and(mb_strlen(Comment::first()->body))->toBe(453);
});

test('nobody can comment while the owner has comments turned off', function () {
    $profile = User::factory()->create(['allow_comments' => false]);

    $this->actingAs(User::factory()->create())->post(route('users.comments.store', $profile), ['body' => 'Hi'])
        ->assertForbidden();
    $this->actingAs($profile)->post(route('users.comments.store', $profile), ['body' => 'Hi'])
        ->assertForbidden();

    expect(Comment::count())->toBe(0);
});

test('a player can post 5 comments a minute', function () {
    $profile = User::factory()->create();
    $author = User::factory()->create();

    foreach (range(1, 5) as $i) {
        $this->actingAs($author)->post(route('users.comments.store', $profile), ['body' => "Comment {$i}"])
            ->assertRedirect(profileCommentsUrl($profile));
    }

    $this->actingAs($author)->post(route('users.comments.store', $profile), ['body' => 'One too many'])
        ->assertTooManyRequests();
    expect(Comment::count())->toBe(5);
});

test('logged-in players get the comment form with a counter', function () {
    $profile = User::factory()->create();

    $html = $this->actingAs(User::factory()->create())->get(route('users.comments', $profile))->getContent();

    expect($html)
        ->toContain('action="'.route('users.comments.store', $profile).'"')
        ->toContain('x-data="charCounter(0)"')
        ->toContain('<label for="comment-body"')
        ->toContain('name="body"')
        ->toContain('maxlength="500"')
        ->toContain('x-text="counter">0 / 500</span>')
        ->toContain('>Post comment</button>')
        ->not->toContain('to leave a comment.');
});

test('a failed comment comes back with the error and what you typed', function () {
    $profile = User::factory()->create();
    $tooLong = str_repeat('b', 501);

    $html = $this->actingAs(User::factory()->create())
        ->from(route('users.comments', $profile))
        ->followingRedirects()
        ->post(route('users.comments.store', $profile), ['body' => $tooLong])
        ->getContent();

    expect($html)
        ->toContain('<p id="comment-body-error" role="alert"')
        ->toContain('aria-invalid="true"')
        ->toContain('>'.$tooLong.'</textarea>')
        ->toContain('x-data="charCounter(501)"')
        ->toContain('501 / 500');
});

test('a failed comment goes back to the page it came from, at the comments', function () {
    $profile = User::factory()->create();
    $tooLong = ['body' => str_repeat('b', 501)];

    $this->actingAs(User::factory()->create())
        ->from(route('users.comments', [$profile, 'page' => 2]))
        ->post(route('users.comments.store', $profile), $tooLong)
        ->assertRedirect(route('users.comments', [$profile, 'page' => 2]).'#comments');

    $this->from(route('users.show', $profile))
        ->post(route('users.comments.store', $profile), $tooLong)
        ->assertRedirect(route('users.show', $profile).'#comments');
});

test('a posted comment goes back to the page it came from, with one fragment', function () {
    $profile = User::factory()->create();
    $author = User::factory()->create();

    $this->actingAs($author)
        ->from(route('users.comments', $profile))
        ->post(route('users.comments.store', $profile), ['body' => 'From the list'])
        ->assertRedirect(route('users.comments', $profile).'#comments');

    $this->actingAs($author)
        ->from(route('users.show', $profile).'#decks')
        ->post(route('users.comments.store', $profile), ['body' => 'From the profile'])
        ->assertRedirect(route('users.show', $profile).'#comments');
});

test('the profile page script is in the build', function () {
    expect(file_get_contents(base_path('vite.config.js')))->toContain("'resources/js/profile-page.js'");
});

test('a posted comment lands on the first page of the list, a failed one keeps its page', function () {
    $profile = User::factory()->create();
    $author = User::factory()->create();

    $this->actingAs($author)
        ->from(route('users.comments', [$profile, 'page' => 2]))
        ->post(route('users.comments.store', $profile), ['body' => 'Hello'])
        ->assertRedirect(route('users.comments', $profile).'#comments');
});
