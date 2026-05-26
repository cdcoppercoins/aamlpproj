<?php

namespace App\Services;

use App\Models\Plate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PlateImageStorage
{
    /**
     * @return array{image_base: string, image_ext: string, has_back_image: int|null}
     */
    public function storeFrontImage(Plate $plate, UploadedFile $file, ?string $imageBase = null): array
    {
        $setCode = $plate->set_code;
        $directory = public_path('plates/' . $setCode);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? $extension : 'jpg';

        $base = $this->resolveImageBase($plate, $imageBase, $extension);

        $file->move($directory, $base . '_a.' . $extension);

        return [
            'image_base' => $base,
            'image_ext' => $extension,
            'has_back_image' => $plate->has_back_image,
        ];
    }

    public function storeBackImage(Plate $plate, UploadedFile $file): void
    {
        if (empty($plate->image_base) || empty($plate->image_ext)) {
            throw new \InvalidArgumentException('Front image metadata is required before uploading a back image.');
        }

        $directory = public_path('plates/' . $plate->set_code);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $file->move($directory, $plate->image_base . '_b.' . $plate->image_ext);

        $plate->forceFill(['has_back_image' => 1])->save();
    }

    public function deleteImages(Plate $plate, bool $front = true, bool $back = true): void
    {
        if (empty($plate->image_base) || empty($plate->image_ext)) {
            return;
        }

        $directory = public_path('plates/' . $plate->set_code);

        if ($front) {
            $frontPath = $directory . '/' . $plate->image_base . '_a.' . $plate->image_ext;
            if (File::isFile($frontPath)) {
                File::delete($frontPath);
            }
        }

        if ($back) {
            $backPath = $directory . '/' . $plate->image_base . '_b.' . $plate->image_ext;
            if (File::isFile($backPath)) {
                File::delete($backPath);
            }
        }
    }

    private function resolveImageBase(Plate $plate, ?string $imageBase, string $extension): string
    {
        $base = trim((string) ($imageBase ?: $plate->image_base ?: ''));

        if ($base === '') {
            $base = $plate->serial_number
                ? Str::slug($plate->serial_number, '_')
                : 'plate_' . $plate->id;
        }

        $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', $base) ?: 'plate_' . $plate->id;
        $base = trim($base, '._-');

        if ($base === '') {
            $base = 'plate_' . $plate->id;
        }

        return $base;
    }
}
