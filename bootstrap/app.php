<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        then: function(){
            Route::middleware('web')
            ->prefix('admin')
            ->name('admin')
            ->group(base_path('routes/admin_routes.php'));

            Route::middleware('web')
            ->prefix('employee')
            ->name('employee')
            ->group(base_path('routes/employee_routes.php'));

            Route::middleware('web')
            ->prefix('hresource')
            ->name('hresource')
            ->group(base_path('routes/hresource_routes.php'));

            Route::middleware('web')
            ->prefix('accounting')
            ->name('accounting')
            ->group(base_path('routes/accounting_routes.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
