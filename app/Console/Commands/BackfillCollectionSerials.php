<?php

namespace App\Console\Commands;

use App\Support\CollectionSerialAssigner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCollectionSerials extends Command
{
    private const SNAPSHOT_PATH = 'collection_serial_backfill_snapshot.json';

    protected $signature = 'collection:backfill-serials
                            {--rollback : Restore serial numbers and counters from the saved snapshot}
                            {--report= : Write a CSV report to this path (default: storage/app/collection_serial_report.csv)}';

    protected $description = 'Assign serial numbers to owned items missing them, with rollback support';

    public function handle(): int
    {
        if ($this->option('rollback')) {
            return $this->rollback();
        }

        return $this->backfill();
    }

    private function backfill(): int
    {
        $missing = $this->missingSerialCount();

        if ($missing === 0) {
            $this->info('No owned items are missing a serial number.');
            $this->writeReport();

            return self::SUCCESS;
        }

        if ($this->snapshotExists()) {
            $this->warn('A rollback snapshot already exists. Run with --rollback first if you need to undo the previous backfill.');
        }

        $this->saveSnapshot();

        $items = DB::table('collection_owned_items as o')
            ->join('collection_items as ci', 'ci.id', '=', 'o.collection_item_id')
            ->join('plates as p', 'p.id', '=', 'ci.plate_id')
            ->where(function ($query) {
                $query->whereNull('o.serial_number')->orWhere('o.serial_number', '');
            })
            ->orderBy('p.year')
            ->orderBy('o.id')
            ->get([
                'o.id',
                'p.year',
            ]);

        $byYear = $items->groupBy(static fn ($row) => CollectionSerialAssigner::normalizeYear(
            $row->year !== null ? (int) $row->year : null
        ));

        $assigned = 0;

        DB::transaction(function () use ($byYear, &$assigned) {
            foreach ($byYear as $year => $yearItems) {
                $serials = CollectionSerialAssigner::nextSerialsForYear((int) $year, $yearItems->count());

                foreach ($yearItems->values() as $index => $item) {
                    DB::table('collection_owned_items')
                        ->where('id', $item->id)
                        ->update(['serial_number' => $serials[$index]]);

                    $assigned++;
                }
            }
        });

        $this->info("Assigned serial numbers to {$assigned} owned item(s).");
        $this->line('Rollback snapshot: storage/app/'.self::SNAPSHOT_PATH);
        $this->line('Undo with: php artisan collection:backfill-serials --rollback');

        $this->writeReport();

        return self::SUCCESS;
    }

    private function rollback(): int
    {
        if (! $this->snapshotExists()) {
            $this->error('No rollback snapshot found at storage/app/'.self::SNAPSHOT_PATH);

            return self::FAILURE;
        }

        $snapshot = json_decode((string) file_get_contents($this->snapshotPath()), true);

        if (! is_array($snapshot)) {
            $this->error('Rollback snapshot is invalid.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($snapshot) {
            foreach ($snapshot['owned_items'] ?? [] as $row) {
                DB::table('collection_owned_items')
                    ->where('id', $row['id'])
                    ->update(['serial_number' => $row['serial_number']]);
            }

            DB::table('collection_serial_sequences')->delete();

            foreach ($snapshot['sequences'] ?? [] as $row) {
                DB::table('collection_serial_sequences')->insert([
                    'year' => $row['year'],
                    'last_value' => $row['last_value'],
                ]);
            }
        });

        unlink($this->snapshotPath());

        $this->info('Rolled back serial numbers and sequence counters from snapshot.');
        $this->line('Snapshot file removed.');

        return self::SUCCESS;
    }

    private function writeReport(): void
    {
        $reportPath = $this->option('report') ?: storage_path('app/collection_serial_report.csv');

        $rows = DB::table('collection_owned_items as o')
            ->join('collection_items as ci', 'ci.id', '=', 'o.collection_item_id')
            ->join('plates as p', 'p.id', '=', 'ci.plate_id')
            ->join('users as u', 'u.id', '=', 'ci.user_id')
            ->orderBy('o.serial_number')
            ->orderBy('o.id')
            ->get([
                'o.id as owned_item_id',
                'o.serial_number',
                'o.grade',
                'p.year',
                'p.set_code',
                'p.cat_ref',
                'p.company',
                'u.username',
            ]);

        $handle = fopen($reportPath, 'w');

        if ($handle === false) {
            $this->error("Could not write report to {$reportPath}");

            return;
        }

        fputcsv($handle, [
            'owned_item_id',
            'serial_number',
            'grade',
            'plate_year',
            'set_code',
            'cat_ref',
            'company',
            'username',
        ]);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->owned_item_id,
                $row->serial_number,
                $row->grade,
                $row->year,
                $row->set_code,
                $row->cat_ref,
                $row->company,
                $row->username,
            ]);
        }

        fclose($handle);

        $this->newLine();
        $this->info('All owned items with serial numbers ('.$rows->count().' rows):');
        $this->table(
            ['Serial', 'Grade', 'Year', 'Set', 'Cat ref', 'User'],
            $rows->map(static fn ($row) => [
                $row->serial_number ?? '(null)',
                $row->grade ?? '',
                $row->year ?? '',
                $row->set_code ?? '',
                $row->cat_ref ?? '',
                $row->username ?? '',
            ])->all()
        );

        $this->line("Full CSV report: {$reportPath}");
    }

    private function saveSnapshot(): void
    {
        $ownedItems = DB::table('collection_owned_items')
            ->orderBy('id')
            ->get(['id', 'serial_number'])
            ->map(static fn ($row) => [
                'id' => $row->id,
                'serial_number' => $row->serial_number,
            ])
            ->all();

        $sequences = DB::table('collection_serial_sequences')
            ->orderBy('year')
            ->get(['year', 'last_value'])
            ->map(static fn ($row) => [
                'year' => $row->year,
                'last_value' => $row->last_value,
            ])
            ->all();

        $payload = json_encode([
            'created_at' => now()->toIso8601String(),
            'owned_items' => $ownedItems,
            'sequences' => $sequences,
        ], JSON_PRETTY_PRINT);

        file_put_contents($this->snapshotPath(), $payload);
    }

    private function missingSerialCount(): int
    {
        return DB::table('collection_owned_items')
            ->where(function ($query) {
                $query->whereNull('serial_number')->orWhere('serial_number', '');
            })
            ->count();
    }

    private function snapshotExists(): bool
    {
        return is_file($this->snapshotPath());
    }

    private function snapshotPath(): string
    {
        return storage_path('app/'.self::SNAPSHOT_PATH);
    }
}
