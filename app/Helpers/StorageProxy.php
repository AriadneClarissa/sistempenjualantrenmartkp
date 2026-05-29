<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageProxy
{
    public static function url(?string $path): string
    {
        if (empty($path)) {
            return asset('images/no-image.png');
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        $public = public_path('storage/' . $path);
        if (file_exists($public)) {
            return asset('storage/' . $path);
        }

        $mediaDisk = (string) config('filesystems.media_disk', 'public');
        if ($mediaDisk !== 'public') {
            try {
                return Storage::disk($mediaDisk)->url($path);
            } catch (\Throwable $e) {
                report($e);

                // Jika driver cloud gagal di production, tampilkan fallback aman agar halaman tidak 500.
                if (Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }
            }
        }

        // If file exists in storage disk, use proxy route
        if (Storage::disk('public')->exists($path)) {
            return url('/storage-proxy/' . $path);
        }

        return asset('images/no-image.png');
    }
}
