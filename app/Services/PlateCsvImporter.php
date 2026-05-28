<?php

namespace App\Services;

use App\Support\PlateCsvColumns;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PlateCsvImporter
{
    private array $jurisdictionTypeMap = [
        'canada' => 'ca_province',
        'foreign' => 'foreign_country',
        'territory' => 'foreign_country',
    ];

    /**
     * @return array{imported: int, skipped: int, errors: list<string>, set_codes: list<string>}
     */
    public function importFromPath(string $path, ?string $expectedSetCode = null): array
    {
        if (! file_exists($path)) {
            return $this->result(0, 0, ["File not found: {$path}"]);
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            return $this->result(0, 0, ["Could not open file: {$path}"]);
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);

            return $this->result(0, 0, ['Could not read CSV headers.']);
        }

        $headers = array_map(function ($header) {
            $normalized = strtolower(trim((string) $header));

            return $normalized === 'image_a' ? 'image_base' : $normalized;
        }, $headers);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $setCodes = [];
        $rowNum = 1;
        $expectedSetCode = $expectedSetCode !== null ? trim($expectedSetCode) : null;
        if ($expectedSetCode === '') {
            $expectedSetCode = null;
        }

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = array_combine($headers, array_slice($row, 0, count($headers)));
            $rowSetCode = trim((string) ($data['set_code'] ?? ''));

            if ($expectedSetCode !== null && $rowSetCode !== '' && strcasecmp($rowSetCode, $expectedSetCode) !== 0) {
                $errors[] = "Row {$rowNum}: set_code must be {$expectedSetCode} (found {$rowSetCode}).";
                $skipped++;

                continue;
            }

            $record = $this->mapRow($data, $rowNum);

            if ($record === null) {
                $skipped++;

                continue;
            }

            try {
                $this->ensureSetDirectory($record['set_code']);
                $this->insertWithDuplicateHandling($record);
                $imported++;
                $setCodes[$record['set_code']] = true;
            } catch (\Throwable $exception) {
                $errors[] = "Row {$rowNum}: {$exception->getMessage()}";
                $skipped++;
            }
        }

        fclose($handle);

        if ($expectedSetCode !== null && count($setCodes) > 1) {
            $errors[] = 'CSV must contain only one set_code when a set code is specified on the upload form.';
        }

        return $this->result($imported, $skipped, $errors, array_keys($setCodes));
    }

    /**
     * @return array{imported: int, skipped: int, errors: list<string>, set_codes: list<string>}
     */
    public function importFromUploadedFile(UploadedFile $file, ?string $expectedSetCode = null): array
    {
        $tempPath = $file->getRealPath();

        if (! $tempPath) {
            return $this->result(0, 0, ['Uploaded file could not be read.']);
        }

        return $this->importFromPath($tempPath, $expectedSetCode);
    }

    public function renderTemplateCsv(array $prefill = []): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, PlateCsvColumns::HEADERS);
        foreach (PlateCsvColumns::exampleRows($prefill) as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }

    private function ensureSetDirectory(string $setCode): void
    {
        $directory = public_path('plates/' . $setCode);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    /**
     * @param  list<string>  $errors
     * @param  list<string>  $setCodes
     * @return array{imported: int, skipped: int, errors: list<string>, set_codes: list<string>}
     */
    private function result(int $imported, int $skipped, array $errors, array $setCodes = []): array
    {
        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'set_codes' => $setCodes,
        ];
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
