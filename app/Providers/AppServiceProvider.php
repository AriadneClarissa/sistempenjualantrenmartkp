<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Wajib dipanggil

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
        // Paksa HTTPS bekerja jika terdeteksi koneksi dari Vercel (Forwarded Proto)
        // atau jika APP_ENV bukan local
        if (request()->header('x-forwarded-proto') === 'https' || env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
    }
}