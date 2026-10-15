<?php

namespace App\Providers;

use App\Models\Deck;
use App\Models\User;
use App\Services\MagicService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

        // Every {series} route segment must be a series from config/series.php.
        Route::pattern('series', implode('|', array_keys(config('series'))));
        Route::pattern('deck', '[0-9]+');

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('username')).'|'.$request->ip()));

        // Liking and unliking, per user. Named so it keeps its own counter: plain `throttle:N,M` routes all share one
        // counter per user (e.g. with deck imports).
        RateLimiter::for('likes', fn (Request $request) => Limit::perMinute(30)->by($request->user()->id));

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
