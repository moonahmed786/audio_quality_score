<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
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
        Paginator::useTailwind();

        RateLimiter::for('upload', function ($request) {
            $userId = $request->user()?->id;
            $key = $userId ? 'user:' . $userId : 'ip:' . $request->ip();

            return Limit::perMinute(10)->by($key);
        });

        RateLimiter::for('api-auth', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
