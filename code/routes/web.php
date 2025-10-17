<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DecryptController;
use App\Http\Controllers\EncryptController;
use App\Http\Controllers\ImageEncryptionController;

Route::get('/', function () {
    return view('pages.home');
});

// Route::get('/', [ImageEncryptionController::class, 'index']);
Route::post('/upload', [ImageEncryptionController::class, 'upload'])->name('upload');
Route::post('/encrypt', [ImageEncryptionController::class, 'encrypt'])->name('encrypt');
Route::post('/decrypt', [ImageEncryptionController::class, 'decrypt'])->name('decrypt');
Route::get('/download/{type}/{filename}', [ImageEncryptionController::class, 'download'])->name('download');
