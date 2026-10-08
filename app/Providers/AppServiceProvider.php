<?php

namespace App\Providers;

use App\Services\MagicService;
use Illuminate\Cache\RateLimiting\Limit;
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
        // Every {series} route segment must be a series from config/series.php.
        Route::pattern('series', implode('|', array_keys(config('series'))));
        Route::pattern('deck', '[0-9]+');

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('username')).'|'.$request->ip()));

        // The series the visitor is browsing, used by the navigation links.
        View::composer('components.layout', function ($view) {
            $series = request()->route('series') ?? session('series');

            $view->with('navSeries', array_key_exists((string) $series, config('series')) ? $series : null);
        });
    }
}
