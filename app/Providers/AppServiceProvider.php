<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; 
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
        // --- PERBAIKAN VERCEL DIMULAI DI SINI ---
        // Menggunakan config() karena env() akan gagal/null saat di-cache oleh Vercel
        if (config('app.env') === 'production') {
            
            // 1. Paksa semua link yang dibuat Laravel (termasuk email) menjadi HTTPS
            URL::forceScheme('https');
            
            // 2. Paksa sistem pembaca URL Laravel agar menganggap koneksi ini murni HTTPS.
            // Ini adalah kunci agar fungsi hasValidSignature() tidak menggagalkan URL!
            request()->server->set('HTTPS', 'on');
        }
        // --- PERBAIKAN VERCEL SELESAI ---


        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Konfirmasi Email Trenmart')
                ->greeting('Halo ' . ($notifiable->name ?? 'Pelanggan') . ',')
                ->line('Terima kasih sudah mendaftar di Trenmart. Silakan verifikasi alamat email Anda untuk melanjutkan.')
                ->action('Verifikasi Email', $url)
                ->line('Jika Anda tidak membuat akun ini, abaikan email ini.');
        });

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