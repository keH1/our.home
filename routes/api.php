<?php

use App\Http\Controllers\OneC;
use App\Http\Procedures\HouseProcedure;
use App\Http\Procedures\UserProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->as('v1:')->group(function () {
    Route::middleware(['rpc.api'])->group(function () {
        Route::rpc('/jsonrpc', [UserProcedure::class, HouseProcedure::class]);
    });

    //1C routes
    Route::middleware('one.c.api')->prefix('document')->as('oneC:')->group(function () {
        Route::post('counters', [OneC::class, 'counters'])->name('counters');
        Route::post('customers', [OneC::class, 'customers'])->name('customers');
    });
});
