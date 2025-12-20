<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\JobPost;
use App\Observers\JobPostObserver;
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
        // JobPostオブザーバーを登録
        JobPost::observe(JobPostObserver::class);
    }
}
