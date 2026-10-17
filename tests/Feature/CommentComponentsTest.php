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

test('a comment by the owner of what it is on gets that target\'s badge', function () {
    $deck = Deck::factory()->public()->create();
    $profile = User::factory()->create();
    $render = fn (Comment $comment) => Blade::render('<x-comment :comment="$comment" />', ['comment' => $comment]);

    expect($render(Comment::factory()->by($deck->user)->on($deck)->create()))->toContain('owner-badge">Deck owner</span>')
        ->and($render(Comment::factory()->on($deck)->create()))->not->toContain('owner-badge')
        ->and($render(Comment::factory()->by($profile)->on($profile)->create()))->toContain('owner-badge">Profile owner</span>');
});

test('the comment box shows the form to players who can post, a log-in line otherwise', function () {
    view()->share('errors', new ViewErrorBag);
    $render = fn (bool $canPost) => Blade::render(
        '<x-comments.box :can-post="$canPost" action="https://example.test/c" :draft="$draft" />',
        ['canPost' => $canPost, 'draft' => new CommentDraft('')],
    );

    expect($render(true))->toContain('action="https://example.test/c"')->not->toContain('to leave a comment')
        ->and($render(false))->toContain('to leave a comment')->not->toContain('<form');
});

test('the comment form posts to the given action and keeps the draft', function () {
    view()->share('errors', new ViewErrorBag); // the web middleware shares it on real pages

    $html = Blade::render('<x-comment-form :action="$action" :draft="$draft" />', [
        'action' => 'https://example.test/decks/1/comments',
        'draft' => new CommentDraft('abc'),
    ]);

    expect($html)
        ->toContain('action="https://example.test/decks/1/comments"')
        ->toContain('x-data="charCounter(3, 500)"')
        ->toContain('x-text="counter">3 / 500</span>')
        ->toContain('>abc</textarea>');
});

test('the list shows the comments or says there are none', function () {
    $profile = User::factory()->create();
    Comment::factory()->on($profile)->create(['body' => 'Nice profile']);
    $render = fn ($comments) => Blade::render(
        '<x-comments.list :comments="$comments" empty-heading="h2" />',
        ['comments' => $comments],
    );

    expect($render($profile->profileComments()->with('author')->get()))->toContain('Nice profile')
        ->and($render(collect()))->toContain('No comments yet')->toContain('<h2');
});
