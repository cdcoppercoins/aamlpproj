<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_owned_items', function (Blueprint $table) {
            $table->string('listing_type', 8)->nullable()->after('notes');
            $table->string('listing_notes', 500)->nullable()->after('listing_type');

            $table->index('listing_type');
        });
    }

    public function down(): void
    {
        Schema::table('collection_owned_items', function (Blueprint $table) {
            $table->dropIndex(['listing_type']);
            $table->dropColumn(['listing_type', 'listing_notes']);
        });
    }
};
