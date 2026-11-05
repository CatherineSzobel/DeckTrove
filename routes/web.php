<?php

use App\Http\Controllers\BulkDeckController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CollectionItemController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeckCollectionController;
use App\Http\Controllers\DeckController;
use App\Http\Controllers\DeckLikeController;
use App\Http\Controllers\DeckTransferController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\PackCollectionController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexController::class)->name('index');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisterUserController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:10,1')->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->middleware('throttle:10,1')->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Deck management
    Route::get('/decks', [DeckController::class, 'mine'])->name('decks.mine');
    Route::post('/decks/bulk', BulkDeckController::class)->name('decks.bulk');
    Route::post('/{series}/deck-builder', [DeckController::class, 'store'])->middleware('throttle:deck-saves')->name('decks.store');
    Route::get('/decks/{deck}/edit', [DeckController::class, 'edit'])->can('update', 'deck')->name('decks.edit');
    Route::patch('/decks/{deck}', [DeckController::class, 'update'])->can('update', 'deck')->middleware('throttle:deck-saves')->name('decks.update');
    Route::delete('/decks/{deck}', [DeckController::class, 'destroy'])->can('delete', 'deck')->name('decks.destroy');

    // Import from other tools' formats, and copy someone's public deck (visibility checked by DeckPolicy::view)
    Route::post('/{series}/decks/import', [DeckTransferController::class, 'import'])->middleware('throttle:10,1')->name('decks.import');
    Route::post('/decks/{deck}/import', [DeckTransferController::class, 'reimport'])->can('update', 'deck')->middleware('throttle:10,1')->name('decks.reimport');
    Route::post('/decks/{deck}/copy', [DeckTransferController::class, 'copy'])->middleware('throttle:deck-saves')->name('decks.copy');

    // Likes on public decks (DeckPolicy::like: not your own deck; someone else's private deck is a 404)
    Route::post('/decks/{deck}/like', [DeckLikeController::class, 'store'])->can('like', 'deck')->middleware('throttle:likes')->name('decks.like');
    Route::delete('/decks/{deck}/like', [DeckLikeController::class, 'destroy'])->can('view', 'deck')->middleware('throttle:likes')->name('decks.unlike');

    // Comments on profiles and decks (StoreCommentRequest: only while the target accepts comments; someone else's
    // private deck is a 404 through DeckPolicy::view)
    Route::post('/u/{user:username}/comments', [CommentController::class, 'store'])->middleware('throttle:comments')->name('users.comments.store');
    Route::post('/decks/{deck}/comments', [CommentController::class, 'store'])->can('view', 'deck')->middleware('throttle:comments')->name('decks.comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->can('delete', 'comment')->name('comments.destroy');

    // Collections and wishlists (App\Collection\CollectionService)
    // A search there also searches the card database (quick add), so it shares the card search's limit.
    Route::get('/collection', [CollectionController::class, 'owned'])->middleware('throttle:card-search')->name('collection');
    Route::get('/collection/wishlist', [CollectionController::class, 'wishlist'])->middleware('throttle:card-search')->name('collection.wishlist');
    Route::post('/collection/add', [CollectionItemController::class, 'add'])->middleware('throttle:collection-items')->name('collection.add');
    Route::post('/collection/remove', [CollectionItemController::class, 'remove'])->middleware('throttle:collection-items')->name('collection.remove');
    Route::post('/collection/want', [CollectionItemController::class, 'want'])->middleware('throttle:collection-items')->name('collection.want');
    Route::post('/decks/{deck}/collection', [DeckCollectionController::class, 'collection'])->can('view', 'deck')->middleware('throttle:collection')->name('decks.collection');
    Route::post('/decks/{deck}/wishlist', [DeckCollectionController::class, 'wishlist'])->can('view', 'deck')->middleware('throttle:collection')->name('decks.wishlist');
});

// Decks (visibility is checked by DeckPolicy::view)
Route::get('/decks/{deck}', [DeckController::class, 'show'])->name('decks.show');
Route::get('/decks/{deck}/export', [DeckTransferController::class, 'export'])->middleware('throttle:card-search')->name('decks.export');
Route::get('/public-deck', [DeckController::class, 'index'])->name('decks.index');
Route::get('/u/{user:username}', [PublicProfileController::class, 'show'])->name('users.show');
Route::get('/u/{user:username}/decks', [PublicProfileController::class, 'decks'])->name('users.decks');
Route::get('/u/{user:username}/liked', [PublicProfileController::class, 'liked'])->name('users.liked');
Route::get('/u/{user:username}/comments', [PublicProfileController::class, 'comments'])->name('users.comments');
Route::get('/u/{user:username}/collection', [PublicProfileController::class, 'collection'])->name('users.collection');
Route::get('/u/{user:username}/wishlist', [PublicProfileController::class, 'wishlist'])->name('users.wishlist');

// Per series
Route::prefix('{series}')->group(function () {
    Route::get('/cards', [CardController::class, 'index'])->middleware('throttle:card-search')->name('cards.index');
    Route::get('/card/{id}', [CardController::class, 'show'])->where('id', '[A-Za-z0-9._-]+')->middleware('throttle:card-search')->name('cards.show');

    Route::get('/packs', [PackController::class, 'index'])->name('packs.index');
    // The optional slug picks between Yu-Gi-Oh! products that share a set code.
    Route::get('/pack/{setCode}/{slug?}', [PackController::class, 'show'])
        ->where(['setCode' => '[A-Za-z0-9-]+', 'slug' => '[a-z0-9-]+'])
        ->middleware('throttle:card-search')
        ->name('packs.show');
    // The slug comes after `collection`: an optional segment in the middle can’t be generated or matched.
    Route::post('/pack/{setCode}/collection/{slug?}', PackCollectionController::class)
        ->where(['setCode' => '[A-Za-z0-9-]+', 'slug' => '[a-z0-9-]+'])
        ->middleware(['auth', 'throttle:collection'])
        ->name('packs.collection');

    // Guests can browse the builder; saving requires login.
    Route::get('/deck-builder', [DeckController::class, 'builder'])->middleware('throttle:card-search')->name('decks.builder');
});
