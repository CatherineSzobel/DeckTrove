<?php

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function commentsPage(User $profile, array $query = [], ?User $viewer = null)
{
    if (! $viewer) {
        app('auth')->forgetGuards();

        return test()->get(route('users.comments', [$profile, ...$query]));
    }

    return test()->actingAs($viewer)->get(route('users.comments', [$profile, ...$query]));
}

test('the comments page has its title and a breadcrumb back to the profile', function () {
    $profile = User::factory()->create(['username' => 'kaiba']);

    expect(commentsPage($profile)->assertOk()->getContent())
        ->toContain('<title>Comments · kaiba · DeckTrove</title>')
        ->toContain('href="'.route('users.show', $profile).'"')
        ->toMatch('/<h1[^>]*>Comments<\/h1>/')
        ->toContain('id="comments"');
});

test('comments are listed newest first with their author and time', function () {
    $this->travelTo('2026-10-15 14:03:00');
    $profile = User::factory()->create();
    $alice = User::factory()->create(['username' => 'alice']);
    Comment::factory()->by($alice)->on($profile)->create(['body' => 'Older comment', 'created_at' => now()->subHours(3)]);
    Comment::factory()->on($profile)->create(['body' => 'Newer comment', 'created_at' => now()->subMinute()]);

    $html = commentsPage($profile)->getContent();

    expect(strpos($html, 'Newer comment'))->toBeLessThan(strpos($html, 'Older comment'))
        ->and($html)
        ->toContain('href="'.route('users.show', $alice).'"')
        ->toContain('<time datetime="2026-10-15T11:03:00+00:00" title="15 October 2026, 11:03"')
        ->toContain('3 hours ago');
});

test('comments show 20 to a page and page links stay on the comments page', function () {
    $profile = User::factory()->create();
    Comment::factory()->on($profile)->count(21)->create();

    $page = commentsPage($profile)->viewData('page');

    expect($page->comments()->count())->toBe(20)
        ->and($page->comments()->nextPageUrl())->toStartWith(route('users.comments', $profile))
        ->and($page->comments()->nextPageUrl())->toEndWith('#comments');
});

test('a comment is plain text with its line breaks kept', function () {
    $profile = User::factory()->create();
    Comment::factory()->on($profile)->create(['body' => "<script>alert(1)</script>\nSecond line"]);

    expect(commentsPage($profile)->getContent())
        ->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->not->toContain('<script>alert(1)')
        ->toContain('whitespace-pre-line');
});

test('the profile owner’s own comments get a badge', function () {
    $profile = User::factory()->create();
    Comment::factory()->by($profile)->on($profile)->create(['body' => 'Thanks all!']);
    Comment::factory()->on($profile)->create(['body' => 'Hello']);

    expect(substr_count(commentsPage($profile)->getContent(), '>Profile owner</span>'))->toBe(1);
});

test('guests are asked to log in to comment and get no form', function () {
    $profile = User::factory()->create();

    expect(commentsPage($profile)->getContent())
        ->toContain('href="'.route('login').'"')
        ->toContain('to leave a comment.')
        ->not->toContain('name="body"');
});

test('a profile without comments says so', function () {
    expect(commentsPage(User::factory()->create())->getContent())->toContain('No comments yet');
});

test('the comments page is not found while the owner has comments off, and comes back when they are on', function () {
    $profile = User::factory()->create(['allow_comments' => false]);
    Comment::factory()->on($profile)->create(['body' => 'Still here']);

    commentsPage($profile)->assertNotFound();
    commentsPage($profile, [], $profile)->assertNotFound();

    $profile->update(['allow_comments' => true]);
    expect(commentsPage($profile->fresh())->getContent())->toContain('Still here');
});

test('a page past the end goes to the last page', function () {
    $profile = User::factory()->create();
    Comment::factory()->count(21)->on($profile)->create();

    commentsPage($profile, ['page' => 5])
        ->assertRedirect(route('users.comments', [$profile, 'page' => 2]).'#comments');
});

test('a page past the end with no comments says so instead of redirecting', function () {
    $profile = User::factory()->create();

    expect(commentsPage($profile, ['page' => 2])->assertOk()->getContent())->toContain('No comments yet')
        ->toMatch('/<h2[^>]*>No comments yet<\/h2>/');
});
