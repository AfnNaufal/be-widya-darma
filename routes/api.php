<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\NewsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes SMA Widya Darma
|--------------------------------------------------------------------------
*/

// Berita & Galeri (Dependent of News & Admin)
Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);
    Route::get('/{slug}', [NewsController::class, 'show']);
});

Route::get('/galleries', [GalleryController::class, 'index']);

// AI Chatbot (Dependent of ChatSession & KnowledgeBase)
Route::prefix('chat')->group(function () {
    Route::post('/send', [ChatController::class, 'sendMessage']);
    Route::get('/history/{visitor_token}', [ChatController::class, 'history']);
});
