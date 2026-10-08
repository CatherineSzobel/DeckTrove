<?php

use App\Http\Controllers\CardsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeckController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\PacksController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
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

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Deck management
    Route::get('/decks', [DeckController::class, 'mine'])->name('decks');
    Route::post('/{series}/deck-builder', [DeckController::class, 'store'])->name('decks.store');
    Route::get('/decks/{deck}/edit', [DeckController::class, 'edit'])->can('update', 'deck')->name('decks.edit');
    Route::patch('/decks/{deck}', [DeckController::class, 'update'])->can('update', 'deck')->name('decks.update');
    Route::delete('/decks/{deck}', [DeckController::class, 'destroy'])->can('delete', 'deck')->name('decks.destroy');
});

// Decks (visibility is checked by DeckPolicy::view)
Route::get('/decks/{deck}', [DeckController::class, 'show'])->name('decks.show');
Route::get('/public-deck', [DeckController::class, 'index'])->name('public-deck');

// Per series
Route::prefix('{series}')->group(function () {
    Route::get('/cards', [CardsController::class, 'index'])->name('cards.index');
    Route::get('/card/{id}', [CardsController::class, 'show'])->where('id', '[0-9a-fA-F-]+')->name('cards.show');

    Route::get('/packs', [PacksController::class, 'index'])->name('packs.index');
    // The optional slug picks between Yu-Gi-Oh! products that share a set code.
    Route::get('/pack/{setCode}/{slug?}', [PacksController::class, 'show'])
        ->where(['setCode' => '[A-Za-z0-9-]+', 'slug' => '[a-z0-9-]+'])
        ->name('packs.show');

    // Guests can browse the builder; saving requires login.
    Route::get('/deck-builder', [DeckController::class, 'builder'])->name('decks.builder');
});
