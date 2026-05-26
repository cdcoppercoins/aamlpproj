<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plate_id')->constrained('plates')->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('condition', 4)->nullable();
            $table->date('acquired_date')->nullable();
            $table->decimal('price_paid', 10, 2)->nullable();
            $table->string('storage_location', 128)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_wanted')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'plate_id']);
            $table->index(['user_id', 'is_wanted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_items');
    }
};
