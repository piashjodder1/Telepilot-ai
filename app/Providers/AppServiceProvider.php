<?php

namespace App\Providers;

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
        try {
            $timezone = \App\Models\Setting::get('timezone', 'Asia/Dhaka');
            date_default_timezone_set($timezone);
            \Illuminate\Support\Facades\Config::set('app.timezone', $timezone);
        } catch (\Exception $e) {
            // Database not ready yet
        }
        // Use global timezone

    }
}
