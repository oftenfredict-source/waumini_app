<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('owner')
                ->name('owner.')
                ->group(base_path('routes/owner.php'));

            Route::middleware('web')
                ->name('church.')
                ->group(base_path('routes/church.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'owner' => \App\Http\Middleware\EnsureOwnerUser::class,
            'church' => \App\Http\Middleware\EnsureChurchUser::class,
            'church.member' => \App\Http\Middleware\EnsureChurchMember::class,
            'church.maintenance' => \App\Http\Middleware\PreventChurchAccessDuringMaintenance::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->is('owner') || $request->is('owner/*')) {
                return route('owner.login');
            }

            return route('church.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
