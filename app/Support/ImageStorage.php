<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class ImageStorage
{
    /**
     * Store an uploaded image cleanly:
     * - In testing/local environments without base64 mode, stores on public disk as expected by feature tests unless configured otherwise.
     * - In production (Render ephemeral disk), resizes via GD and encodes as a compact WebP/JPEG data URI.
     */
    public static function storeImage(UploadedFile $file, string $directory, int $maxWidth = 360, int $quality = 80): string
    {
        if (! app()->environment('production')) {
            return $file->store($directory, 'public');
        }

        $realPath = $file->getRealPath();
        $rawContents = $realPath ? @file_get_contents($realPath) : false;

        if ($rawContents !== false && function_exists('imagecreatefromstring')) {
            $src = @imagecreatefromstring($rawContents);
            if ($src !== false) {
                $width = imagesx($src);
                $height = imagesy($src);

                if ($width > $maxWidth && $width > 0) {
                    $newWidth = $maxWidth;
                    $newHeight = max(1, (int) round($height * ($maxWidth / $width)));
                    $dst = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagedestroy($src);
                    $src = $dst;
                }

                ob_start();
                if (function_exists('imagewebp')) {
                    imagewebp($src, null, $quality);
                    $mime = 'image/webp';
                } else {
                    imagejpeg($src, null, $quality);
                    $mime = 'image/jpeg';
                }
                $compressed = ob_get_clean();
                imagedestroy($src);

                if ($compressed !== false && $compressed !== '') {
                    return 'data:' . $mime . ';base64,' . base64_encode($compressed);
                }
            }
        }

        $mime = $file->getMimeType() ?: 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode((string) $rawContents);
    }
}