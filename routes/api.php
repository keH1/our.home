<?php

use App\Http\Procedures\UserProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->as('v1:')->group(function () {
    Route::middleware(['rpc.api'])->group(function (){
        Route::rpc('/jsonrpc', [UserProcedure::class]);
    });
});
