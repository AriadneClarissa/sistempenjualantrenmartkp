<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaStorage
{
    public static function disk(): string
    {
        return (string) config('filesystems.media_disk', 'public');
    }

    public static function uploadImage(UploadedFile $file, string $folder): string
    {
        return $file->store(trim($folder, '/'), self::disk());
    }

    public static function delete(?string $path): void
    {
        if (empty($path) || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        $disk = self::disk();
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
            return;
        }

        // Backward compatibility: data lama mungkin masih disimpan di disk public.
        if ($disk !== 'public' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}