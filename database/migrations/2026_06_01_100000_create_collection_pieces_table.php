<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_item_id')->constrained('collection_items')->cascadeOnDelete();
            $table->string('grade', 4)->nullable();
            $table->string('serial_number', 32)->nullable();
            $table->date('acquired_date')->nullable();
            $table->decimal('price_paid', 10, 2)->nullable();
            $table->string('storage_location', 128)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['collection_item_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_pieces');
    }
};
