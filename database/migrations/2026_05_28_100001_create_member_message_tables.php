<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_message_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_low_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('participant_high_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owned_item_id')->constrained('collection_owned_items')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['participant_low_id', 'participant_high_id', 'owned_item_id'],
                'member_message_threads_participants_item_unique'
            );
        });

        Schema::create('member_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('member_message_threads')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
            $table->index(['sender_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_messages');
        Schema::dropIfExists('member_message_threads');
    }
};
