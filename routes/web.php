<?php

use App\Http\Controllers\CardsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\DeckController;
use App\Models\Deck;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\PacksController;
use App\Http\Controllers\ProfileController;


Route::get('/', [IndexController::class, 'index'])->name('index');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');
// Public Pages
Route::get('/portfolio', fn() => view('portfolio'))->name('portfolio');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/user/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/user/edit', [ProfileController::class, 'update'])->name('profile.update'); // <-- add this
});
Route::middleware('auth')->put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::get('/decks', function () {

    if (!Auth::check()) {
        return redirect()->route('login');
    }

    // Authenticated user
    $user = Auth::user();

    // Fetch decks belonging to this user
    $decks = Deck::where('user_id', $user->id)->get();

    return view('decks.mydecks', compact('decks'));
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

Route::get('/{series}/cards', [CardsController::class, 'index'])
    ->whereIn('series', ['magic', 'yugioh'])
    ->name('cards.index');

Route::get('/{series}/card/{id}', [CardsController::class, 'show'])
    ->whereIn('series', ['magic', 'yugioh'])
    ->name('cards.show');

Route::get('/{series}/packs', [PacksController::class, 'index'])
    ->whereIn('series', ['magic', 'yugioh'])
    ->name('packs.index');
Route::get('/{series}/pack/{setCode}', [PacksController::class, 'show'])
    ->whereIn('series', ['magic', 'yugioh'])
    ->name('packs.show');


// Magic Routes
Route::prefix('magic')->group(function () {
    Route::get('/', fn() => view('home'));

    // Deck builder routes
    Route::get('/deck-builder', [DeckController::class, 'builder'])
        ->name('magic.deck.builder');
    Route::post('/deck-builder/save', [DeckController::class, 'save'])
        ->name('magic.deck.builder.save')
        ->middleware('auth')
        ->defaults('game', 'magic');
});

// Yu-Gi-Oh Routes
Route::prefix('yugioh')->group(function () {

    Route::get('/deck-builder', [DeckController::class, 'builder'])
        ->name('yugioh.deck.builder');
    Route::post('/deck-builder/save', [DeckController::class, 'save'])
        ->name('yugioh.deck.builder.save')
        ->defaults('game', 'yugioh');
});
