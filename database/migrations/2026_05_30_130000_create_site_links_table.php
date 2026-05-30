<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_links', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('placement', 40);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['placement', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_links');
    }
};
