<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DecryptController;
use App\Http\Controllers\EncryptController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/chacha/encrypt', [EncryptController::class, 'encrypt']);
Route::post('/chacha/decrypt', [DecryptController::class, 'decrypt']);

