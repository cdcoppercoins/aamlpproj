<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news_signups')) {
            return;
        }

        Schema::rename('news_signups', 'newsletter_subscribers');

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->renameColumn('joined_at', 'subscribed_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            return;
        }

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->renameColumn('subscribed_at', 'joined_at');
        });

        Schema::rename('newsletter_subscribers', 'news_signups');
    }
};
