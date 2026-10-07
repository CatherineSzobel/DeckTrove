<?php

use App\Exceptions\ScryfallUnavailableException;
use App\Http\Middleware\RememberSeries;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [RememberSeries::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ScryfallUnavailableException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 503);
            }

            return response()->view('errors.503', ['message' => $e->getMessage()], 503);
        });
    })->create();
