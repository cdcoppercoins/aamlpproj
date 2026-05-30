<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class ImageOptimizer
{
    /**
     * Resize and recompress an image file in place. Returns false if skipped or failed.
     */
    public static function optimize(string $filePath, string $profile = 'default'): bool
    {
        if (! extension_loaded('gd') || ! is_file($filePath)) {
            return false;
        }

        $info = @getimagesize($filePath);
        if ($info === false) {
            return false;
        }

        [$width, $height, $type] = $info;

        if ($type === IMAGETYPE_GIF) {
            return false;
        }

        $options = self::options($profile);
        $source = self::loadImage($filePath, $type);

        if ($source === null) {
            return false;
        }

        [$targetWidth, $targetHeight] = self::fitWithin($width, $height, $options['max_width'], $options['max_height']);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($canvas === false) {
            imagedestroy($source);

            return false;
        }

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        $saved = self::saveImage($canvas, $filePath, $type, $options);
        imagedestroy($canvas);

        return $saved;
    }

    /**
     * @return array{max_width: int, max_height: int, jpeg_quality: int, webp_quality: int, png_compression: int}
     */
    private static function options(string $profile): array
    {
        return match ($profile) {
            'history' => ['max_width' => 900, 'max_height' => 600, 'jpeg_quality' => 82, 'webp_quality' => 80, 'png_compression' => 7],
            'hero' => ['max_width' => 1400, 'max_height' => 800, 'jpeg_quality' => 85, 'webp_quality' => 82, 'png_compression' => 7],
            'plate' => ['max_width' => 1000, 'max_height' => 1000, 'jpeg_quality' => 88, 'webp_quality' => 85, 'png_compression' => 7],
            'profile' => ['max_width' => 400, 'max_height' => 400, 'jpeg_quality' => 85, 'webp_quality' => 82, 'png_compression' => 7],
            'article' => ['max_width' => 1200, 'max_height' => 900, 'jpeg_quality' => 82, 'webp_quality' => 80, 'png_compression' => 7],
            'page' => ['max_width' => 1200, 'max_height' => 900, 'jpeg_quality' => 82, 'webp_quality' => 80, 'png_compression' => 7],
            default => ['max_width' => 1200, 'max_height' => 1200, 'jpeg_quality' => 82, 'webp_quality' => 80, 'png_compression' => 7],
        };
    }

    /**
     * @return array{0: int, 1: int}
     */
    private static function fitWithin(int $width, int $height, int $maxWidth, int $maxHeight): array
    {
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return [$width, $height];
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }

    private static function loadImage(string $filePath, int $type): ?\GdImage
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath) ?: null,
            IMAGETYPE_PNG => @imagecreatefrompng($filePath) ?: null,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($filePath) ?: null) : null,
            default => null,
        };
    }

    /**
     * @param  array{jpeg_quality: int, webp_quality: int, png_compression: int}  $options
     */
    private static function saveImage(\GdImage $canvas, string $filePath, int $type, array $options): bool
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagejpeg($canvas, $filePath, $options['jpeg_quality']),
            IMAGETYPE_PNG => imagepng($canvas, $filePath, $options['png_compression']),
            IMAGETYPE_WEBP => function_exists('imagewebp')
                ? imagewebp($canvas, $filePath, $options['webp_quality'])
                : false,
            default => false,
        };
    }

    /**
     * Optimize every image under a public-relative folder (e.g. articles-media).
     */
    public static function optimizeDirectory(string $relativeFolder, string $profile): int
    {
        $directory = WebPublicPath::path($relativeFolder);

        if (! File::isDirectory($directory)) {
            return 0;
        }

        $count = 0;

        foreach (File::files($directory) as $file) {
            if (! preg_match('/\.(jpe?g|png|webp)$/i', $file->getFilename())) {
                continue;
            }

            if (self::optimize($file->getPathname(), $profile)) {
                $count++;
            }
        }

        return $count;
    }

    public static function countInDirectory(string $relativeFolder): int
    {
        $directory = WebPublicPath::path($relativeFolder);

        if (! File::isDirectory($directory)) {
            return 0;
        }

        $count = 0;

        foreach (File::files($directory) as $file) {
            if (preg_match('/\.(jpe?g|png|webp)$/i', $file->getFilename())) {
                $count++;
            }
        }

        return $count;
    }

    public static function countPlates(?string $setCode = null): int
    {
        $root = WebPublicPath::path('plates');

        if (! File::isDirectory($root)) {
            return 0;
        }

        $count = 0;
        $setDirs = $setCode !== null && $setCode !== ''
            ? [$root . DIRECTORY_SEPARATOR . $setCode]
            : File::directories($root);

        foreach ($setDirs as $setDir) {
            if (! File::isDirectory($setDir)) {
                continue;
            }

            foreach (File::files($setDir) as $file) {
                if (preg_match('/\.(jpe?g|png|webp)$/i', $file->getFilename())) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Optimize all plate images under public/plates/{set}/ (one folder per set).
     */
    public static function optimizePlates(?string $setCode = null): int
    {
        $root = WebPublicPath::path('plates');

        if (! File::isDirectory($root)) {
            return 0;
        }

        $count = 0;
        $setDirs = $setCode !== null && $setCode !== ''
            ? [$root . DIRECTORY_SEPARATOR . $setCode]
            : File::directories($root);

        foreach ($setDirs as $setDir) {
            if (! File::isDirectory($setDir)) {
                continue;
            }

            foreach (File::files($setDir) as $file) {
                if (! preg_match('/\.(jpe?g|png|webp)$/i', $file->getFilename())) {
                    continue;
                }

                if (self::optimize($file->getPathname(), 'plate')) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
