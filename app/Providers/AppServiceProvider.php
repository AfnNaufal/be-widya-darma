<?php

namespace App\Providers;

use App\Models\Extracurricular;
use App\Models\Gallery;
use App\Models\KnowledgeBase;
use App\Models\News;
use App\Models\Program;
use App\Models\SchoolProfile;
use App\Models\Spmb;
use App\Models\Teacher;
use App\Observers\ActivityLogObserver;
use App\Services\ImageUploadService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate limiter khusus untuk AI Chatbot (NFR-04 & Security Hardening: Anti-Spoofing via IP)
        RateLimiter::for('chatbot', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan chat dalam waktu singkat. Silakan tunggu sebentar sebelum mengirim pesan kembali.',
                ], 429);
            });
        });

        foreach ([
            SchoolProfile::class,
            Program::class,
            Extracurricular::class,
            Spmb::class,
            News::class,
            Gallery::class,
            KnowledgeBase::class,
            Teacher::class,
        ] as $loggedModel) {
            $loggedModel::observe(ActivityLogObserver::class);
        }

        // Otomatis hapus file .webp lama di storage saat foto di-update atau data dihapus (agar server tetap ringan)
        $imageColumns = ['image_url', 'logo_url', 'image', 'brochure_url'];
        foreach ([SchoolProfile::class, Program::class, Extracurricular::class, Spmb::class, News::class, Gallery::class, Teacher::class] as $mediaModel) {
            $mediaModel::updating(function (Model $model) use ($imageColumns) {
                $uploader = app(ImageUploadService::class);
                foreach ($imageColumns as $col) {
                    if ($model->isDirty($col)) {
                        $uploader->deleteStoredImage($model->getOriginal($col));
                    }
                }
            });

            $mediaModel::deleting(function (Model $model) use ($imageColumns) {
                $uploader = app(ImageUploadService::class);
                foreach ($imageColumns as $col) {
                    $val = $model->getAttribute($col);
                    if (is_string($val)) {
                        $uploader->deleteStoredImage($val);
                    }
                }
            });
        }
    }
}
