<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('interval_ms')->default(7000);
            $table->timestamps();
        });

        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('image', 255);
            $table->string('alt', 255);
            $table->string('headline', 255);
            $table->string('subline', 500)->nullable();
            $table->string('cta', 128)->nullable();
            $table->string('route', 64)->nullable();
            $table->json('route_params')->nullable();
            $table->string('bg', 512);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('hero_settings')->insert([
            'interval_ms' => (int) config('home_hero.interval_ms', 7000),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (config('home_hero.slides', []) as $index => $slide) {
            DB::table('hero_slides')->insert([
                'sort_order' => $index + 1,
                'image' => $slide['image'],
                'alt' => $slide['alt'],
                'headline' => $slide['headline'],
                'subline' => $slide['subline'] ?? null,
                'cta' => $slide['cta'] ?? null,
                'route' => $slide['route'] ?? null,
                'route_params' => ! empty($slide['route_params'])
                    ? json_encode($slide['route_params'])
                    : null,
                'bg' => $slide['bg'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
        Schema::dropIfExists('hero_settings');
    }
};
