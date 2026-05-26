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
        $disk = self::disk();
        $storedPath = $file->store(trim($folder, '/'), $disk);

        if ($disk === 'cloudinary') {
            return Storage::disk($disk)->url($storedPath);
        }

        return $storedPath;
    }

    public static function delete(?string $path): void
    {
        if (empty($path) || filter_var($path, FILTER_VALIDATE_URL)) {
            if (!self::isCloudinaryUrl($path)) {
                return;
            }
        }

        $disk = self::disk();
        $deleteTarget = self::normalizeDeleteTarget($path);

        if ($deleteTarget !== null && Storage::disk($disk)->exists($deleteTarget)) {
            Storage::disk($disk)->delete($deleteTarget);
            return;
        }

        // Backward compatibility: data lama mungkin masih disimpan di disk public.
        if ($deleteTarget !== null && $disk !== 'public' && Storage::disk('public')->exists($deleteTarget)) {
            Storage::disk('public')->delete($deleteTarget);
        }
    }

    private static function normalizeDeleteTarget(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (!self::isCloudinaryUrl($path)) {
            return null;
        }

        $parsedPath = (string) parse_url($path, PHP_URL_PATH);
        $uploadPosition = strpos($parsedPath, '/upload/');

        if ($uploadPosition === false) {
            return null;
        }

        $relativePath = substr($parsedPath, $uploadPosition + strlen('/upload/'));
        $relativePath = preg_replace('#^v\d+/#', '', $relativePath);

        return $relativePath !== '' ? $relativePath : null;
    }

    private static function isCloudinaryUrl(?string $path): bool
    {
        return is_string($path) && str_contains($path, 'res.cloudinary.com');
    }
}