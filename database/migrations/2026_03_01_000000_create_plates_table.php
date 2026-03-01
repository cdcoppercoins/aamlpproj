<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Schema from GALLERY_CATALOG_SPEC.md and docs/PLATE_CSV_COLUMNS_REFERENCE.html
     */
    public function up(): void
    {
        Schema::create('plates', function (Blueprint $table) {
            $table->id();
            $table->string('set_code', 64);
            $table->string('set_name', 255);
            $table->string('cat_ref', 10)->nullable();
            $table->string('company', 128)->nullable();
            $table->string('image_base', 128)->nullable();
            $table->string('image_ext', 16)->nullable();
            $table->tinyInteger('has_back_image')->nullable();
            $table->string('jurisdiction', 128)->nullable();
            $table->string('jurisdiction_type', 32)->nullable();
            $table->integer('year')->nullable();
            $table->string('serial_number', 64)->nullable();
            $table->decimal('width_inches', 10, 4)->nullable();
            $table->decimal('height_inches', 10, 4)->nullable();
            $table->string('value_mt', 32)->nullable();
            $table->string('value_ex', 32)->nullable();
            $table->string('value_vg', 32)->nullable();
            $table->string('value_g', 32)->nullable();
            $table->string('value_fr', 32)->nullable();
            $table->string('value_po', 32)->nullable();
            $table->string('variety_key', 32)->nullable();
            $table->text('variety_notes')->nullable();
            $table->tinyInteger('state_embossed')->nullable();
            $table->tinyInteger('legend_embossed')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->nullable()->default(0);
            $table->timestamps();

            $table->unique(['set_code', 'image_base', 'variety_key'], 'plates_set_code_image_base_variety_key_unique');
            $table->index('set_code');
            $table->index('has_back_image');
            $table->index('jurisdiction');
            $table->index('jurisdiction_type');
            $table->index('year');
            $table->index('serial_number');
            $table->index('width_inches');
            $table->index('height_inches');
            $table->index('set_name');
            $table->index('cat_ref');
            $table->index('company');
            $table->index('variety_key');
            $table->index('state_embossed');
            $table->index('legend_embossed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plates');
    }
};
