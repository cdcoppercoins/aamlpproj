<?php

namespace App\Services;

use App\Support\ImageOptimizer;
use App\Support\WebPublicPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HeroImageStorage
{
    public function store(UploadedFile $file): string
    {
        $directory = WebPublicPath::path('hero');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? $extension : 'jpg';

        $filename = Str::uuid()->toString() . '.' . $extension;
        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        $file->move($directory, $filename);
        ImageOptimizer::optimize($fullPath, 'hero');

        return 'hero/' . $filename;
    }

    public function delete(?string $imagePath): void
    {
        if ($imagePath === null || $imagePath === '') {
            return;
        }

        if (! str_starts_with($imagePath, 'hero/')) {
            return;
        }

        $fullPath = WebPublicPath::path($imagePath);

        if (File::isFile($fullPath)) {
            File::delete($fullPath);
        }
    }
}
