<?php

use App\Http\Middleware\EnsureUserHasRole;
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
        // Short alias so routes can say 'role:student'
        $middleware->alias(['role' => EnsureUserHasRole::class]);

        // Not logged in -> go to the login page
        $middleware->redirectGuestsTo('/login');

        // Already logged in but opened /login or /register -> go to own dashboard
        $middleware->redirectUsersTo(
            fn (Request $request) => $request->user()->role->homePath()
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();