<?php

use App\Http\Middleware\OneCMiddleware;
use App\Http\Middleware\RpcApiMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            Route::middleware('api')
                 ->domain('api.nash-dom-96.ru')
                 ->group(base_path('routes/api.php'));

            Route::middleware('web')
                 ->domain('nash-dom-96.ru')
                 ->group(base_path('routes/web.php'));
        },
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'rpc.api'=>RpcApiMiddleware::class,
            'one.c.api'=> OneCMiddleware::class
        ]);
        $middleware->trustProxies(at: [
            '172.18.0/16',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
