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
    Route::middleware(['custom.sanctum','auth:sanctum'])->group(function (){
        Route::rpc('/jsonrpc', [UserProcedure::class])->name('rpc.user');
    });
});



/*Route::prefix('/v1/jsonrpc/')->group(function () {
    Route::post('register',[LoginController::class,'register'])->name('register');
    Route::post('get_user_data',[LoginController::class,'getUserData'])->middleware('auth:sanctum')->name('get_user_data');
    Route::post('login',[LoginController::class,'login'])->name('login');
    Route::post('logout',[LoginController::class,'logout'])->middleware('auth:sanctum')->name('logout');
});*/
