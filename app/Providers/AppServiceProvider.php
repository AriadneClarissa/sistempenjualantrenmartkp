<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Wajib dipanggil
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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

        // Share notification counters and recent notifications with all views
        View::composer('*', function ($view) {
            $user = Auth::user();

            if ($user) {
                $notificationUnreadCount = $user->unreadNotifications()->count();

                // Load recent notifications (limit to 50) and convert to lightweight array for JS
                $recentNotifications = $user->notifications()
                    ->orderBy('created_at', 'desc')
                    ->take(50)
                    ->get()
                    ->map(function ($n) {
                        return [
                            'id' => $n->id,
                            'data' => $n->data ?? [],
                            'read_at' => $n->read_at?->toDateTimeString(),
                            'created_at' => $n->created_at?->toDateTimeString(),
                        ];
                    });
            } else {
                $notificationUnreadCount = 0;
                $recentNotifications = collect();
            }

            // Bundling warnings are set elsewhere for admin views; default to null here
            $bundling_warnings = null;

            $view->with(compact('notificationUnreadCount', 'recentNotifications', 'bundling_warnings'));
        });
    }
}