<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Assigns permanent, globally unique serial numbers to owned collection items.
 *
 * Format: {plate year}{5-digit sequence}, e.g. 193800001. Sequences increment per
 * catalog year, never reuse a number, and identify one physical item site-wide.
 */
class CollectionSerialAssigner
{
    /**
     * @return list<string>
     */
    public static function nextSerialsForYear(?int $year, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $year = self::normalizeYear($year);

        return DB::transaction(function () use ($year, $count) {
            $row = DB::table('collection_serial_sequences')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                $start = self::maxExistingSequence($year) + 1;
                $end = $start + $count - 1;

                DB::table('collection_serial_sequences')->insert([
                    'year' => $year,
                    'last_value' => $end,
                ]);
            } else {
                $start = ((int) $row->last_value) + 1;
                $end = $start + $count - 1;

                DB::table('collection_serial_sequences')
                    ->where('year', $year)
                    ->update(['last_value' => $end]);
            }

            $serials = [];
            for ($sequence = $start; $sequence <= $end; $sequence++) {
                $serials[] = self::format($year, $sequence);
            }

            foreach ($serials as $serial) {
                self::assertUnique($serial);
            }

            return $serials;
        });
    }

    public static function nextSerialForYear(?int $year): string
    {
        return self::nextSerialsForYear($year, 1)[0];
    }

    public static function format(int $year, int $sequence): string
    {
        return $year.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    public static function normalizeYear(?int $year): int
    {
        if ($year !== null && $year >= 1000 && $year <= 9999) {
            return $year;
        }

        return (int) date('Y');
    }

    public static function isTaken(string $serial, ?int $exceptOwnedItemId = null): bool
    {
        $query = DB::table('collection_owned_items')->where('serial_number', $serial);

        if ($exceptOwnedItemId !== null) {
            $query->where('id', '!=', $exceptOwnedItemId);
        }

        return $query->exists();
    }

    public static function assertUnique(string $serial, ?int $exceptOwnedItemId = null): void
    {
        if (self::isTaken($serial, $exceptOwnedItemId)) {
            throw new RuntimeException("Collection serial number [{$serial}] is already assigned.");
        }
    }

    private static function maxExistingSequence(int $year): int
    {
        $prefix = (string) $year;
        $max = 0;

        foreach (DB::table('collection_owned_items')->whereNotNull('serial_number')->pluck('serial_number') as $serial) {
            if (! preg_match('/^'.preg_quote($prefix, '/').'(\d{5})$/', (string) $serial, $matches)) {
                continue;
            }

            $max = max($max, (int) $matches[1]);
        }

        $counter = DB::table('collection_serial_sequences')
            ->where('year', $year)
            ->value('last_value');

        return max($max, (int) $counter);
    }
}
