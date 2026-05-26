<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('profile_image');
            $table->boolean('is_blocked')->default(false)->after('is_admin');
            $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            $table->text('blocked_reason')->nullable()->after('blocked_at');
        });

        $bootstrapEmail = config('admin.bootstrap_email');

        if ($bootstrapEmail) {
            DB::table('users')
                ->where('email', $bootstrapEmail)
                ->update(['is_admin' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_blocked', 'blocked_at', 'blocked_reason']);
        });
    }
};
