<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\HandleInertiaRequests;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        // Add your middlewares here, including the CSRF Token validation one
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '*'  // This disables CSRF validation for all routes
        ]);
    })
    ->withExceptions(function ($exceptions) {

    })
    ->create();
