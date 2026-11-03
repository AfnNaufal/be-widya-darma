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
