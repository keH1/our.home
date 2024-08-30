<?php

use App\Http\Middleware\JsonRpcMethodResolver;
use App\Http\Procedures\TennisProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->as('v1:')->group(function () {
    Route::rpc('/jsonrpc', [TennisProcedure::class])->name('rpc.tennis');
});
