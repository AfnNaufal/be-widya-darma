<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\ExtracurricularController as AdminExtracurricularController;
use App\Http\Controllers\Api\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Api\Admin\KnowledgeBaseController as AdminKnowledgeBaseController;
use App\Http\Controllers\Api\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Api\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Api\Admin\SchoolProfileController as AdminSchoolProfileController;
use App\Http\Controllers\Api\Admin\SpmbController as AdminSpmbController;
use App\Http\Controllers\Api\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Api\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ExtracurricularController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\SchoolProfileController;
use App\Http\Controllers\Api\SpmbController;
use App\Http\Controllers\Api\TeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes (Untuk Pengunjung Website SMA Widya Darma)
|--------------------------------------------------------------------------
*/

Route::get('/profile', [SchoolProfileController::class, 'show']);

Route::get('/teachers', [TeacherController::class, 'index']);
Route::get('/teachers/{teacher}', [TeacherController::class, 'show']);

Route::get('/programs', [ProgramController::class, 'index']);
Route::get('/programs/{program}', [ProgramController::class, 'show']);

Route::get('/extracurriculars', [ExtracurricularController::class, 'index']);
Route::get('/extracurriculars/{extracurricular}', [ExtracurricularController::class, 'show']);

Route::get('/spmb', [SpmbController::class, 'index']);
Route::get('/spmb/{spmb}', [SpmbController::class, 'show']);

// Berita, Publikasi & Galeri Kegiatan (Mendukung filter ?category=Berita|Jurnal|Karya|Prestasi|Ekstrakurikuler)
Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);
    Route::get('/{slug}', [NewsController::class, 'show']);
});

Route::prefix('activities')->group(function () {
    Route::get('/', [NewsController::class, 'index']);
    Route::get('/{slug}', [NewsController::class, 'show']);
});

Route::get('/galleries', [GalleryController::class, 'index']);

// AI Chatbot (Dilengkapi Rate Limiter 60 request / menit sesuai NFR-04)
Route::prefix('chat')->middleware('throttle:chatbot')->group(function () {
    Route::post('/send', [ChatController::class, 'sendMessage']);
    Route::get('/history/{visitor_token}', [ChatController::class, 'history']);
});

/*
|--------------------------------------------------------------------------
| Admin CRUD API Routes (Untuk Pengelola Sekolah)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth.admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/me', [AdminAuthController::class, 'me']);

        // Dashboard Summary & Chatbot Analytics (SRS FR-21, FR-22, FR-24)
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/dashboard/chatbot-stats', [AdminDashboardController::class, 'chatbotStats']);

        // Image Upload & WebP Compression
        Route::post('/upload', [AdminUploadController::class, 'store']);

        // School Profile (Single Record Upsert)
        Route::get('/profile', [AdminSchoolProfileController::class, 'show']);
        Route::post('/profile', [AdminSchoolProfileController::class, 'upsert']);
        Route::put('/profile', [AdminSchoolProfileController::class, 'upsert']);

        // Resource CRUD
        Route::apiResource('teachers', AdminTeacherController::class);
        Route::apiResource('programs', AdminProgramController::class);
        Route::apiResource('extracurriculars', AdminExtracurricularController::class);
        Route::apiResource('spmb', AdminSpmbController::class);
        Route::apiResource('news', AdminNewsController::class);
        Route::apiResource('activities', AdminNewsController::class)->parameters(['activities' => 'news']);
        Route::apiResource('galleries', AdminGalleryController::class);
        Route::apiResource('knowledge-bases', AdminKnowledgeBaseController::class);
    });
});
