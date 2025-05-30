<?php

use App\Http\Controllers\OneC;
use App\Http\Controllers\UserDataController;
use App\Http\Procedures\ClientProcedure;
use App\Http\Procedures\HouseProcedure;
use App\Http\Procedures\NotificationProcedure;
use App\Http\Procedures\UserProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Procedures\CounterProcedure;
use \App\Http\Procedures\PaidServiceProcedure;
use \App\Http\Procedures\PaidServiceCategoryProcedure;
use App\Http\Procedures\ClaimProcedure;
use App\Http\Procedures\WorkerProcedure;
use App\Http\Procedures\WorkerCategoryProcedure;
use \App\Http\Procedures\ClaimMessageProcedure;
use \App\Http\Procedures\ClaimCategoryProcedure;
use App\Http\Procedures\ClaimReviewProcedure;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/get_user_data_by_phone', [UserDataController::class, 'getUserDataByPhone'])->middleware('user_data.api')->name('get_user_data_by_phone');

Route::prefix('v1')->as('v1:')->group(function () {
    Route::middleware(['rpc.api'])->group(function () {
        Route::rpc('/jsonrpc', app('rpc.procedures'));
    });

    //1C routes
    Route::middleware('one.c.api')->prefix('document')->as('oneC:')->group(function () {
        Route::post('counters', [OneC::class, 'counters'])->name('counters');
//        Route::post('customers', [OneC::class, 'customers'])->name('customers');
        Route::post('customerNumbers', [OneC::class, 'customerNumbers'])->name('customerNumbers');
        Route::post('apartments', [OneC::class, 'apartments'])->name('apartments');
        Route::post('houses', [OneC::class, 'houses'])->name('houses');
    });
});
