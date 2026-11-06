<?php

namespace App\Providers;

use App\Models\Deck;
use App\Models\User;
use App\Policies\CollectionPolicy;
use App\Services\MagicService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MagicService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Polymorphic types are stored as these keys, not class names. Enforced: an unmapped model fails loudly.
        Relation::enforceMorphMap(['user' => User::class, 'deck' => Deck::class]);

        // Outside production a relation that wasn't eager loaded throws, so an N+1 query fails the tests instead of
        // slowing a page down.
        Model::preventLazyLoading(! $this->app->isProduction());

        // The password rule for registering, changing and resetting a password (Password::defaults()).
        Password::defaults(fn () => Password::min(8));

        // Closures with a nullable user, so guests reach the policy (an array callback would deny them outright).
        Gate::define('viewCollection', fn (?User $viewer, User $owner) => app(CollectionPolicy::class)->viewCollection($viewer, $owner));
        Gate::define('viewWishlist', fn (?User $viewer, User $owner) => app(CollectionPolicy::class)->viewWishlist($viewer, $owner));

        // Every {series} route segment must be a series from config/series.php.
        Route::pattern('series', implode('|', array_keys(config('series'))));
        Route::pattern('deck', '[0-9]+');

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('username')).'|'.$request->ip()));

        // Every throttled route uses a named limiter: plain `throttle:N,M` routes all share one counter per user (or
        // per IP for guests), so one form would use up another's budget.

        // Guest forms, per IP.
        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('password-email', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // Changing the password and deleting the account both check the current password, so they're limited like
        // logging in: someone at a logged-in browser can't guess it.
        RateLimiter::for('current-password', fn (Request $request) => Limit::perMinute(5)->by($request->user()->id));

        RateLimiter::for('profile', fn (Request $request) => Limit::perMinute(10)->by($request->user()->id));

        // Deck imports and re-imports, per user: each looks every card up.
        RateLimiter::for('imports', fn (Request $request) => Limit::perMinute(10)->by($request->user()->id));

        // Liking and unliking, per user.
        RateLimiter::for('likes', fn (Request $request) => Limit::perMinute(30)->by($request->user()->id));

        // Saving and copying decks, per user: each can look every card up at Scryfall.
        RateLimiter::for('deck-saves', fn (Request $request) => Limit::perMinute(20)->by($request->user()->id));

        // Comments, per user: enough for a conversation, not for flooding a profile.
        RateLimiter::for('comments', fn (Request $request) => Limit::perMinute(5)->by($request->user()->id));

        // Collections, per user: + and − on single cards, and the bulk adds (a whole deck or set) that call the card
        // source for every card.
        RateLimiter::for('collection-items', fn (Request $request) => Limit::perMinute(120)->by($request->user()->id));
        RateLimiter::for('collection', fn (Request $request) => Limit::perMinute(30)->by($request->user()->id));

        // Card search and lookups (the card database, card and pack pages, the builder's search), so bots can't burn
        // the Scryfall allowance or load the database. Per account when logged in, else per IP; prefixed so the two
        // never share a counter.
        RateLimiter::for('card-search', fn (Request $request) => $request->user()
            ? Limit::perMinute(120)->by('user:'.$request->user()->id)
            : Limit::perMinute(60)->by('ip:'.$request->ip()));

        // The series the visitor is browsing, used by the navigation links.
        View::composer('components.layout', function ($view) {
            $routeSeries = request()->route('series');
            $series = $routeSeries ?? session('series');
            $isSeries = fn ($key) => is_string($key) && array_key_exists($key, config('series'));

            $view->with([
                'navSeries' => $isSeries($series) ? $series : null,
                // Game pages (routes with a valid {series}) get the game switch unless they place it themselves.
                'routeSeries' => $isSeries($routeSeries) ? $routeSeries : null,
            ]);
        });
    }
}
