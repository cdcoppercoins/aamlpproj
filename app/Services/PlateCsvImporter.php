<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PlateCsvImporter
{
    private array $jurisdictionTypeMap = [
        'canada' => 'ca_province',
        'foreign' => 'foreign_country',
        'territory' => 'foreign_country',
    ];

    /**
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function importFromPath(string $path): array
    {
        if (! file_exists($path)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ["File not found: {$path}"]];
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ["Could not open file: {$path}"]];
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);

            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Could not read CSV headers.']];
        }

        $headers = array_map(function ($header) {
            $normalized = strtolower(trim((string) $header));

            return $normalized === 'image_a' ? 'image_base' : $normalized;
        }, $headers);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = array_combine($headers, array_slice($row, 0, count($headers)));
            $record = $this->mapRow($data, $rowNum);

            if ($record === null) {
                $skipped++;

                continue;
            }

            try {
                $this->insertWithDuplicateHandling($record);
                $imported++;
            } catch (\Throwable $exception) {
                $errors[] = "Row {$rowNum}: {$exception->getMessage()}";
                $skipped++;
            }
        }

        fclose($handle);

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function importFromUploadedFile(UploadedFile $file): array
    {
        $tempPath = $file->getRealPath();

        if (! $tempPath) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Uploaded file could not be read.']];
        }

        return $this->importFromPath($tempPath);
    }

    private function mapRow(array $data, int $rowNum): ?array
    {
        $setCode = trim($data['set_code'] ?? '');
        $setName = trim($data['set_name'] ?? '');

        if ($setCode === '' || $setName === '') {
            return null;
        }

        $imageBase = trim($data['image_base'] ?? $data['image_a'] ?? '');
        if ($imageBase === '') {
            $imageBase = 'nophoto-' . $rowNum;
        }

        $varietyKey = trim($data['variety_key'] ?? '');
        if ($varietyKey === '') {
            $varietyKey = 'base';
        }

        $jurisdictionType = trim($data['jurisdiction_type'] ?? '');
        if (isset($this->jurisdictionTypeMap[$jurisdictionType])) {
            $jurisdictionType = $this->jurisdictionTypeMap[$jurisdictionType];
        }

        return [
            'set_code' => $setCode,
            'set_name' => $setName,
            'cat_ref' => $this->emptyToNull($data['cat_ref'] ?? null),
            'company' => $this->emptyToNull($data['company'] ?? null),
            'image_base' => $imageBase,
            'image_ext' => $this->emptyToNull($data['image_ext'] ?? null),
            'has_back_image' => $this->parseTinyInt($data['has_back_image'] ?? null),
            'jurisdiction' => $this->emptyToNull($data['jurisdiction'] ?? null),
            'jurisdiction_type' => $jurisdictionType ?: null,
            'year' => $this->parseInt($data['year'] ?? null),
            'serial_number' => $this->emptyToNull($data['serial_number'] ?? null),
            'width_inches' => $this->parseDecimal($data['width_inches'] ?? null),
            'height_inches' => $this->parseDecimal($data['height_inches'] ?? null),
            'value_mt' => $this->emptyToNull($data['value_mt'] ?? null),
            'value_ex' => $this->emptyToNull($data['value_ex'] ?? null),
            'value_vg' => $this->emptyToNull($data['value_vg'] ?? null),
            'value_g' => $this->emptyToNull($data['value_g'] ?? null),
            'value_fr' => $this->emptyToNull($data['value_fr'] ?? null),
            'value_po' => $this->emptyToNull($data['value_po'] ?? null),
            'variety_key' => $varietyKey,
            'variety_notes' => $this->emptyToNull($data['variety_notes'] ?? null),
            'state_embossed' => $this->parseTinyInt($data['state_embossed'] ?? null),
            'legend_embossed' => $this->parseTinyInt($data['legend_embossed'] ?? null),
            'notes' => $this->emptyToNull($data['notes'] ?? null),
            'sort_order' => $this->parseInt($data['sort_order'] ?? null) ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function insertWithDuplicateHandling(array $record): void
    {
        $varietyKey = $record['variety_key'];
        $suffix = 2;

        while (true) {
            try {
                DB::table('plates')->insert($record);

                return;
            } catch (\Illuminate\Database\QueryException $exception) {
                if ($exception->getCode() !== '23000' || strpos($exception->getMessage(), 'Duplicate entry') === false) {
                    throw $exception;
                }

                $record['variety_key'] = $varietyKey . '-' . $suffix;
                $suffix++;
            }
        }
    }

    private function emptyToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function parseTinyInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) trim((string) $value);
    }

    private function parseInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : (int) $value;
    }

    private function parseDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : (float) $value;
    }
}
