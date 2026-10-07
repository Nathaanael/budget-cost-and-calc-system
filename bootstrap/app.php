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
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'password.changed' => \App\Http\Middleware\EnsurePasswordIsChanged::class,
            'superadmin' => \App\Http\Middleware\EnsureUserIsSuperadmin::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SetActivePlant::class,
        ]);
        $middleware->prependToPriorityList(
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetActivePlant::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
