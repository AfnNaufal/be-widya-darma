<?php

namespace App\Providers;

use App\Models\Extracurricular;
use App\Models\Gallery;
use App\Models\KnowledgeBase;
use App\Models\News;
use App\Models\Program;
use App\Models\SchoolProfile;
use App\Models\Spmb;
use App\Observers\ActivityLogObserver;
use Illuminate\Cache\RateLimiting\Limit;
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
        // Rate limiter khusus untuk AI Chatbot (NFR-04)
        RateLimiter::for('chatbot', function (Request $request) {
            $key = (string) ($request->input('visitor_token') ?: $request->ip());

            return Limit::perMinute(60)->by($key)->response(function () {
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
        ] as $loggedModel) {
            $loggedModel::observe(ActivityLogObserver::class);
        }
    }
}
