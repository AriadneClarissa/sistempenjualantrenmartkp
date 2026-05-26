<?php

namespace App\Helpers;

use Cloudinary\Cloudinary;
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
        if ($disk === 'cloudinary') {
            try {
                $response = app(Cloudinary::class)->uploadApi()->upload($file->getRealPath(), [
                    'folder' => trim($folder, '/'),
                ]);

                $secureUrl = (string) $response->offsetGet('secure_url');

                if ($secureUrl !== '') {
                    return $secureUrl;
                }
            } catch (\Throwable $e) {
                report($e);
                throw $e;
            }
        }

        return $file->store(trim($folder, '/'), $disk);
    }

    public static function delete(?string $path): void
    {
        if (empty($path) || filter_var($path, FILTER_VALIDATE_URL)) {
            if (!self::isCloudinaryUrl($path)) {
                return;
            }
        }

        try {
            $disk = self::disk();
            $deleteTarget = self::normalizeDeleteTarget($path);

            if ($deleteTarget !== null) {
                Storage::disk($disk)->delete($deleteTarget);

                // Backward compatibility: data lama mungkin masih disimpan di disk public.
                if ($disk !== 'public') {
                    Storage::disk('public')->delete($deleteTarget);
                }
            }
        } catch (\Throwable $e) {
            report($e);
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