<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_items', function (Blueprint $table) {
            $table->json('copy_conditions')->nullable()->after('condition');
        });

        DB::table('collection_items')
            ->where('is_wanted', false)
            ->where('quantity', '>', 0)
            ->whereNotNull('condition')
            ->where('condition', '!=', '')
            ->orderBy('id')
            ->each(function ($row) {
                $copies = array_fill(0, (int) $row->quantity, $row->condition);

                DB::table('collection_items')
                    ->where('id', $row->id)
                    ->update(['copy_conditions' => json_encode($copies)]);
            });
    }

    public function down(): void
    {
        Schema::table('collection_items', function (Blueprint $table) {
            $table->dropColumn('copy_conditions');
        });
    }
};
