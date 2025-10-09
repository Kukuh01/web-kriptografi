<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DecryptController;
use App\Http\Controllers\EncryptController;

Route::get('/', function () {
    return view('pages.home');
});

Route::post('/encrypt', [EncryptController::class, 'encrypt']);
Route::post('/decrypt', [DecryptController::class, 'decrypt']);