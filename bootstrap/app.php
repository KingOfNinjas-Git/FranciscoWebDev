<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\App\Http\Middleware\RedirectWww::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // Sessions (and their cookies) only where needed: see LazyStartSession
        $middleware->web(
            append: [\App\Http\Middleware\SetLocale::class],
            replace: [
                \Illuminate\Session\Middleware\StartSession::class => \App\Http\Middleware\LazyStartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class => \App\Http\Middleware\LazyShareErrors::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class => \App\Http\Middleware\LazyCsrf::class,
            ],
        );
        // The contact form is protected by its own signed token (App\Support\FormToken), not by a session
        $middleware->validateCsrfTokens(except: ['contact']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
