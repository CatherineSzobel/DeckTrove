<?php

use App\Comments\CommentDraft;
use App\Models\Comment;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;

uses(RefreshDatabase::class);

test('a draft counts characters, not bytes', function () {
    $draft = new CommentDraft("héllo\nyou");

    expect($draft->text())->toBe("héllo\nyou")
        ->and($draft->length())->toBe(9)
        ->and($draft->counter())->toBe('9 / 500')
        ->and((new CommentDraft(''))->counter())->toBe('0 / 500');
});

test('a draft comes from the old input, ignoring anything but text', function () {
    $request = Request::create('/');
    $request->setLaravelSession($session = app('session.store'));

    $session->flashInput(['body' => 'Typed before']);
    expect(CommentDraft::fromOld($request)->text())->toBe('Typed before');

    $session->flashInput(['body' => ['x']]);
    expect(CommentDraft::fromOld($request)->text())->toBe('');
});

test('a comment shows the owner badge it is given', function () {
    $deck = Deck::factory()->public()->create();
    $byOwner = Comment::factory()->by($deck->user)->on($deck)->create();
    $byOther = Comment::factory()->on($deck)->create();
    $render = fn (Comment $comment) => Blade::render(
        '<x-comment :comment="$comment" :owner-id="$ownerId" owner-badge="Deck owner" />',
        ['comment' => $comment, 'ownerId' => $deck->user_id],
    );

    expect($render($byOwner))->toContain('owner-badge">Deck owner</span>')
        ->and($render($byOther))->not->toContain('Deck owner');
});

test('the comment form posts to the given action and keeps the draft', function () {
    view()->share('errors', new ViewErrorBag); // the web middleware shares it on real pages

    $html = Blade::render('<x-comment-form :action="$action" :draft="$draft" />', [
        'action' => 'https://example.test/decks/1/comments',
        'draft' => new CommentDraft('abc'),
    ]);

    expect($html)
        ->toContain('action="https://example.test/decks/1/comments"')
        ->toContain('x-data="charCounter(3)"')
        ->toContain('x-text="counter">3 / 500</span>')
        ->toContain('>abc</textarea>');
});

test('the list shows the comments or says there are none', function () {
    $profile = User::factory()->create();
    Comment::factory()->on($profile)->create(['body' => 'Nice profile']);
    $render = fn ($comments) => Blade::render(
        '<x-comments.list :comments="$comments" :owner-id="$ownerId" owner-badge="Profile owner" empty-heading="h2" />',
        ['comments' => $comments, 'ownerId' => $profile->id],
    );

    expect($render($profile->profileComments()->with('author')->get()))->toContain('Nice profile')
        ->and($render(collect()))->toContain('No comments yet')->toContain('<h2');
});
