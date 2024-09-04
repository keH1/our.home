<?php

use App\Http\Controllers\LoginController;
use App\Http\Procedures\UserProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JsonRpcMethodResolver;
use App\Http\Procedures\TennisProcedure;
use \App\Http\Middleware\CustomSanctumMiddleware;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->as('v1:')->group(function () {
    Route::middleware(['custom.sanctum'])->group(function (){
        Route::rpc('/jsonrpc', [UserProcedure::class]);
    });
});
