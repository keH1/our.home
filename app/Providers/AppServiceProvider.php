<?php

namespace App\Providers;

use App\Http\Controllers\JsonRpcController;
use App\Models\Notification;
use App\Observers\NotificationObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Route::macro('rpc', fn(string $uri, array $procedures = [], ?string $delimiter = null) => Route::post($uri,
            [JsonRpcController::class, '__invoke'])->setDefaults([
            'procedures' => $procedures, 'delimiter' => $delimiter,
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Notification::observe(NotificationObserver::class);
    }
}
