<?php

use App\Support\HistoryTimelineConfigImporter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_timeline_entries', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('label', 255);
            $table->string('title', 255);
            $table->longText('body');
            $table->string('image', 255)->nullable();
            $table->string('alt', 255)->nullable();
            $table->text('caption')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        HistoryTimelineConfigImporter::importIfEmpty();
    }

    public function down(): void
    {
        Schema::dropIfExists('history_timeline_entries');
    }
};
