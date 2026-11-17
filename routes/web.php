<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Melayani file gambar hasil kompresi WebP dari storage publik agar langsung tampil di halaman publik
// meskipun perintah `php artisan storage:link` belum dijalankan di komputer lokal.
Route::get('/storage/{path}', function (string $path) {
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path, null, [
        'Cache-Control' => 'public, max-age=31536000',
        'Access-Control-Allow-Origin' => '*',
    ]);
})->where('path', '.*')->withoutMiddleware('web')->name('storage.local');
