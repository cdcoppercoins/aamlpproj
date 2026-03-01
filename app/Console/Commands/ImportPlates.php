<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPlates extends Command
{
    protected $signature = 'plates:import 
                            {file=docs/Mini Plate Checklist - all_plates.csv : Path to CSV file}
                            {--truncate : Truncate plates table before import}';

    protected $description = 'Import plates from CSV into the plates table';

    private array $jurisdictionTypeMap = [
        'canada' => 'ca_province',
        'foreign' => 'foreign_country',
        'territory' => 'foreign_country',
    ];

    public function handle(): int
    {
        $path = base_path($this->argument('file'));

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        if ($this->option('truncate')) {
            DB::table('plates')->truncate();
            $this->info('Plates table truncated.');
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->error("Could not open file: {$path}");
            return 1;
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            $this->error('Could not read CSV headers.');
            return 1;
        }

        // Normalize headers: imAge_A (typo in source) -> image_base
        $headers = array_map(function ($h) {
            return strtolower($h) === 'image_a' ? 'image_base' : strtolower(trim($h));
        }, $headers);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum = $imported + $skipped + 1;
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }
            $data = array_combine($headers, array_slice($row, 0, count($headers)));

            $record = $this->mapRow($data, $rowNum);
            if (!$record) {
                $skipped++;
                continue;
            }

            try {
                $this->insertWithDuplicateHandling($record);
                $imported++;
                if ($imported % 500 === 0) {
                    $this->line("Imported {$imported} rows...");
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        $this->info("Import complete. Imported: {$imported}, Skipped: {$skipped}");
        if (!empty($errors)) {
            foreach (array_slice($errors, 0, 10) as $err) {
                $this->warn($err);
            }
            if (count($errors) > 10) {
                $this->warn('... and ' . (count($errors) - 10) . ' more errors.');
            }
        }

        return 0;
    }

    private function mapRow(array $data, int $rowNum): ?array
    {
        $setCode = trim($data['set_code'] ?? '');
        $setName = trim($data['set_name'] ?? '');
        if (empty($setCode) || empty($setName)) {
            return null;
        }

        $imageBase = trim($data['image_base'] ?? $data['image_a'] ?? '');
        if (empty($imageBase)) {
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

        $hasBack = $this->parseTinyInt($data['has_back_image'] ?? null);
        $stateEmbossed = $this->parseTinyInt($data['state_embossed'] ?? null);
        $legendEmbossed = $this->parseTinyInt($data['legend_embossed'] ?? null);

        $year = $this->parseInt($data['year'] ?? null);
        $sortOrder = $this->parseInt($data['sort_order'] ?? null);

        $widthInches = $this->parseDecimal($data['width_inches'] ?? null);
        $heightInches = $this->parseDecimal($data['height_inches'] ?? null);

        return [
            'set_code' => $setCode,
            'set_name' => $setName,
            'cat_ref' => $this->emptyToNull($data['cat_ref'] ?? null),
            'company' => $this->emptyToNull($data['company'] ?? null),
            'image_base' => $imageBase,
            'image_ext' => $this->emptyToNull($data['image_ext'] ?? null),
            'has_back_image' => $hasBack,
            'jurisdiction' => $this->emptyToNull($data['jurisdiction'] ?? null),
            'jurisdiction_type' => $jurisdictionType ?: null,
            'year' => $year,
            'serial_number' => $this->emptyToNull($data['serial_number'] ?? null),
            'width_inches' => $widthInches,
            'height_inches' => $heightInches,
            'value_mt' => $this->emptyToNull($data['value_mt'] ?? null),
            'value_ex' => $this->emptyToNull($data['value_ex'] ?? null),
            'value_vg' => $this->emptyToNull($data['value_vg'] ?? null),
            'value_g' => $this->emptyToNull($data['value_g'] ?? null),
            'value_fr' => $this->emptyToNull($data['value_fr'] ?? null),
            'value_po' => $this->emptyToNull($data['value_po'] ?? null),
            'variety_key' => $varietyKey,
            'variety_notes' => $this->emptyToNull($data['variety_notes'] ?? null),
            'state_embossed' => $stateEmbossed,
            'legend_embossed' => $legendEmbossed,
            'notes' => $this->emptyToNull($data['notes'] ?? null),
            'sort_order' => $sortOrder ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function emptyToNull(?string $v): ?string
    {
        $v = trim((string) $v);
        return $v === '' ? null : $v;
    }

    private function parseTinyInt($v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        return (int) $v;
    }

    private function parseInt($v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        return (int) $v;
    }

    private function parseDecimal($v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        return (float) $v;
    }

    private function insertWithDuplicateHandling(array $record): void
    {
        $varietyKey = $record['variety_key'];
        $suffix = 2;

        while (true) {
            try {
                DB::table('plates')->insert($record);
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() !== '23000' || strpos($e->getMessage(), 'Duplicate entry') === false) {
                    throw $e;
                }
                $record['variety_key'] = $varietyKey . '-' . $suffix;
                $suffix++;
            }
        }
    }
}
