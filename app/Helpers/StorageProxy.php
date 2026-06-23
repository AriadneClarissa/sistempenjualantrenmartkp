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
            // If using Cloudinary but credentials are missing/invalid, avoid calling the adapter
            if ($mediaDisk === 'cloudinary') {
                $cloudName = env('CLOUDINARY_CLOUD_NAME');
                $apiKey = env('CLOUDINARY_API_KEY');
                $apiSecret = env('CLOUDINARY_API_SECRET');
                if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
                    // don't attempt Cloudinary calls when credentials missing, return placeholder
                    return asset('images/no-image.png');
                }
            }

            try {
                return Storage::disk($mediaDisk)->url($path);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // If file exists in storage disk, use proxy route
        if (Storage::disk('public')->exists($path)) {
            return url('/storage-proxy/' . $path);
        }

        return asset('images/no-image.png');
    }
}
