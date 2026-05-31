<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('collection_pieces', 'collection_owned_items');
    }

    public function down(): void
    {
        Schema::rename('collection_owned_items', 'collection_pieces');
    }
};
