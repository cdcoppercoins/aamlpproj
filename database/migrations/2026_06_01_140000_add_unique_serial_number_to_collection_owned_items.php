<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_owned_items', function (Blueprint $table) {
            $table->unique('serial_number', 'collection_owned_items_serial_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('collection_owned_items', function (Blueprint $table) {
            $table->dropUnique('collection_owned_items_serial_number_unique');
        });
    }
};
