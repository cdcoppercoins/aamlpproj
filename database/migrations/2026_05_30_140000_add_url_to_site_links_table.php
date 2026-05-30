<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_links', function (Blueprint $table) {
            $table->dropForeign(['page_id']);
        });

        Schema::table('site_links', function (Blueprint $table) {
            $table->foreignId('page_id')->nullable()->change();
            $table->string('url', 500)->nullable()->after('page_id');
        });

        Schema::table('site_links', function (Blueprint $table) {
            $table->foreign('page_id')->references('id')->on('pages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_links', function (Blueprint $table) {
            $table->dropForeign(['page_id']);
        });

        Schema::table('site_links', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->foreignId('page_id')->nullable(false)->change();
        });

        Schema::table('site_links', function (Blueprint $table) {
            $table->foreign('page_id')->references('id')->on('pages')->cascadeOnDelete();
        });
    }
};
