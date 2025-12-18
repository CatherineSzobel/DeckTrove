<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YugiohPackController;
use App\Http\Controllers\MagicController;
use App\Http\Controllers\MagicPackController;
use App\Http\Controllers\YugiohController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\DeckController;
use App\Models\Deck;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndexController;

Route::get('/', [IndexController::class, 'index'])->name('index');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');
// Public Pages
Route::get('/portfolio', fn() => view('portfolio'))->name('portfolio');
Route::get('/profile', fn() => view('account.profile'))->name('profile');
Route::get('/decks', function () {

    if (!Auth::check()) {
        return redirect()->route('login');
    }

    // Authenticated user
    $user = Auth::user();

    // Fetch decks belonging to this user
    $decks = Deck::where('user_id', $user->id)->get();

    return view('account.mydecks', compact('decks'));
})->name('decks');
Route::get('/decks/{id}', [DeckController::class, 'show'])->name('decks.show');

//@TODO
Route::get('/decks/{deck}/edit', [DeckController::class, 'edit'])->name('decks.edit');
Route::patch('/decks/{deck}', [DeckController::class, 'update'])->name('decks.update');
Route::delete('/decks/{deck}', [DeckController::class, 'destroy'])
    ->name('decks.destroy');

// Public decks
Route::get('/public-deck', [DeckController::class, 'index'])->name('public-deck');
Route::get('/public-deck/filter', [DeckController::class, 'filter'])->name('public-deck.filter');



// Auth
Route::get('/register', [RegisterUserController::class, 'create'])->name('register')->middleware('guest');
Route::post('/register', [RegisterUserController::class, 'store'])->middleware('guest');
Route::get('/login', [SessionController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [SessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth');

// Magic Routes
Route::prefix('magic')->group(function () {
    Route::get('/', fn() => view('home'));
    Route::get('/cards', [MagicController::class, 'index'])->name('magic.cards.index');
    Route::get('/card/{card}', [MagicController::class, 'show'])->name('magic.cards.show');
    Route::get('/packs', [MagicPackController::class, 'index'])->name('magic.packs.index');
    Route::get('/pack/{pack}', [MagicPackController::class, 'show'])->name('magic.packs.show');

    // Deck builder routes
    Route::get('/deck-builder', [DeckController::class, 'builder'])
        ->name('magic.deck.builder');
    Route::post('/deck-builder/save', [DeckController::class, 'save'])
        ->name('magic.deck.builder.save')
        ->defaults('game', 'magic');
});

// Yu-Gi-Oh Routes
Route::prefix('yugioh')->group(function () {
    Route::get('/cards', [YugiohController::class, 'index'])->name('yugioh.cards.index');
    Route::get('/card/{card}', [YugiohController::class, 'show'])->name('yugioh.cards.show');
    Route::get('/packs', [YugiohPackController::class, 'index'])->name('yugioh.packs.index');
    Route::get('/pack/{pack}', [YugiohPackController::class, 'show'])->name('yugioh.packs.show');

    Route::get('/deck-builder', [DeckController::class, 'builder'])
        ->name('yugioh.deck.builder');
    Route::post('/deck-builder/save', [DeckController::class, 'save'])
        ->name('yugioh.deck.builder.save')
        ->defaults('game', 'yugioh');
});
