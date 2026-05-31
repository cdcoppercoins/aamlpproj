<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_serial_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last_value')->default(0);
        });

        $years = DB::table('collection_owned_items')
            ->join('collection_items', 'collection_items.id', '=', 'collection_owned_items.collection_item_id')
            ->join('plates', 'plates.id', '=', 'collection_items.plate_id')
            ->whereNotNull('plates.year')
            ->distinct()
            ->pluck('plates.year');

        foreach ($years as $year) {
            $year = (int) $year;
            $last = $this->maxSequenceForYear($year);

            if ($last > 0) {
                DB::table('collection_serial_sequences')->insert([
                    'year' => $year,
                    'last_value' => $last,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_serial_sequences');
    }

    private function maxSequenceForYear(int $year): int
    {
        $prefix = (string) $year;
        $max = 0;

        foreach (DB::table('collection_owned_items')->whereNotNull('serial_number')->pluck('serial_number') as $serial) {
            if (! preg_match('/^'.preg_quote($prefix, '/').'(\d{5})$/', (string) $serial, $matches)) {
                continue;
            }

            $max = max($max, (int) $matches[1]);
        }

        return $max;
    }
};
