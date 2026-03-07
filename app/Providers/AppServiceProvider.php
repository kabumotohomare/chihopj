<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\JobPost;
use App\Observers\JobPostObserver;
use App\Policies\ActivityLogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

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
        // JobPostオブザーバーを登録
        JobPost::observe(JobPostObserver::class);

        // vendor モデルのため Gate に明示的にポリシーを登録
        Gate::policy(Activity::class, ActivityLogPolicy::class);
    }
}
