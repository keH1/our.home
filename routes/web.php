<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    dd(Auth::attempt(['phone'=>'+79533183821','password'=>'Mint_Hunter1'])) ;
    return view('welcome');
});
