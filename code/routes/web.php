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

use App\Http\Controllers\DescriptionController;

// Halaman 'About' yang dilihat publik
Route::get('/about', [DescriptionController::class, 'showAbout'])->name('about.show');

// Halaman admin untuk mengedit deskripsi 'About'
Route::get('/admin/about/edit', [DescriptionController::class, 'editAbout'])->name('about.edit');

// Aksi untuk menyimpan/update deskripsi
Route::post('/admin/descriptions/update', [DescriptionController::class, 'update'])->name('descriptions.update');
