<?php

namespace App\Services;

use App\Support\ImageOptimizer;
use App\Support\WebPublicPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HistoryImageStorage
{
    public function store(UploadedFile $file, ?string $preferredBasename = null): string
    {
        $directory = WebPublicPath::path('history-media');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? $extension : 'jpg';

        if ($preferredBasename !== null && $preferredBasename !== '') {
            $basename = Str::slug($preferredBasename, '-');
            $filename = $basename . '.' . $extension;
            $target = $directory . DIRECTORY_SEPARATOR . $filename;
            if (File::isFile($target)) {
                $filename = $basename . '-' . Str::lower(Str::random(6)) . '.' . $extension;
            }
        } else {
            $filename = Str::uuid()->toString() . '.' . $extension;
        }

        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        $file->move($directory, $filename);
        ImageOptimizer::optimize($fullPath, 'history');

        return 'history-media/' . $filename;
    }

    public function delete(?string $imagePath): void
    {
        if ($imagePath === null || $imagePath === '') {
            return;
        }

        if (! str_starts_with($imagePath, 'history-media/')) {
            return;
        }

        $fullPath = WebPublicPath::path($imagePath);

        if (File::isFile($fullPath)) {
            File::delete($fullPath);
        }
    }
}
