<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $items = DB::table('collection_items')->orderBy('id')->get();

        foreach ($items as $item) {
            if ($item->is_wanted) {
                continue;
            }

            $quantity = max(0, (int) $item->quantity);
            if ($quantity <= 0) {
                continue;
            }

            $grades = $this->gradesForItem($item, $quantity);
            $now = now();

            if ($quantity === 1) {
                DB::table('collection_pieces')->insert([
                    'collection_item_id' => $item->id,
                    'grade' => $grades[0] ?? null,
                    'serial_number' => null,
                    'acquired_date' => $item->acquired_date,
                    'price_paid' => $item->price_paid,
                    'storage_location' => $item->storage_location,
                    'notes' => $item->notes,
                    'sort_order' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('collection_items')
                    ->where('id', $item->id)
                    ->update(['notes' => null]);

                continue;
            }

            for ($index = 0; $index < $quantity; $index++) {
                DB::table('collection_pieces')->insert([
                    'collection_item_id' => $item->id,
                    'grade' => $grades[$index] ?? null,
                    'serial_number' => null,
                    'acquired_date' => null,
                    'price_paid' => null,
                    'storage_location' => null,
                    'notes' => null,
                    'sort_order' => $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('collection_items', function (Blueprint $table) {
            $table->dropColumn([
                'quantity',
                'condition',
                'copy_conditions',
                'acquired_date',
                'price_paid',
                'storage_location',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('collection_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('quantity')->default(1)->after('plate_id');
            $table->string('condition', 4)->nullable()->after('quantity');
            $table->json('copy_conditions')->nullable()->after('condition');
            $table->date('acquired_date')->nullable()->after('copy_conditions');
            $table->decimal('price_paid', 10, 2)->nullable()->after('acquired_date');
            $table->string('storage_location', 128)->nullable()->after('price_paid');
        });

        $items = DB::table('collection_items')->orderBy('id')->get();

        foreach ($items as $item) {
            $pieces = DB::table('collection_pieces')
                ->where('collection_item_id', $item->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($pieces->isEmpty()) {
                DB::table('collection_items')
                    ->where('id', $item->id)
                    ->update(['quantity' => $item->is_wanted ? 0 : 0]);

                continue;
            }

            $grades = $pieces->pluck('grade')->all();
            $first = $pieces->first();

            DB::table('collection_items')
                ->where('id', $item->id)
                ->update([
                    'quantity' => $pieces->count(),
                    'condition' => collect($grades)->first(static fn ($grade) => $grade !== null),
                    'copy_conditions' => json_encode($grades),
                    'acquired_date' => $pieces->count() === 1 ? $first->acquired_date : null,
                    'price_paid' => $pieces->count() === 1 ? $first->price_paid : null,
                    'storage_location' => $pieces->count() === 1 ? $first->storage_location : null,
                ]);
        }

        Schema::dropIfExists('collection_pieces');
    }

    /**
     * @return list<string|null>
     */
    private function gradesForItem(object $item, int $quantity): array
    {
        $valid = ['MT', 'EX', 'VG', 'G', 'FR', 'PO'];
        $stored = json_decode($item->copy_conditions ?? '[]', true);
        $grades = [];

        if (is_array($stored)) {
            for ($index = 0; $index < $quantity; $index++) {
                $grade = $stored[$index] ?? null;
                $grades[] = ($grade && in_array($grade, $valid, true)) ? $grade : null;
            }

            if (array_filter($grades)) {
                return $grades;
            }
        }

        if ($item->condition && in_array($item->condition, $valid, true)) {
            return array_fill(0, $quantity, $item->condition);
        }

        return array_fill(0, $quantity, null);
    }
};
