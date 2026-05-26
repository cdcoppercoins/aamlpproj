<?php

namespace App\Console\Commands;

use App\Services\PlateCsvImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPlates extends Command
{
    protected $signature = 'plates:import
                            {file=Mini Plate Checklist - all_plates.csv : Path to CSV file}
                            {--truncate : Truncate plates table before import}';

    protected $description = 'Import plates from CSV into the plates table';

    public function handle(): int
    {
        $path = base_path($this->argument('file'));

        if ($this->option('truncate')) {
            DB::table('plates')->truncate();
            $this->info('Plates table truncated.');
        }

        $result = app(PlateCsvImporter::class)->importFromPath($path);

        if (! empty($result['errors']) && $result['imported'] === 0 && $result['skipped'] === 0) {
            foreach ($result['errors'] as $error) {
                $this->error($error);
            }

            return 1;
        }

        $this->info("Import complete. Imported: {$result['imported']}, Skipped: {$result['skipped']}");
        foreach (array_slice($result['errors'], 0, 10) as $err) {
            $this->warn($err);
        }
        if (count($result['errors']) > 10) {
            $this->warn('... and ' . (count($result['errors']) - 10) . ' more errors.');
        }

        return 0;
    }
}
