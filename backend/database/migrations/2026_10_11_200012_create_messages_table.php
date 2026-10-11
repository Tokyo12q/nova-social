<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('media_url', 2048)->nullable();
            $table->enum('media_type', ['image', 'video', 'file'])->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['conversation_id', 'created_at'], 'idx_messages_conversation_created');
            $table->index('sender_id', 'idx_messages_sender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
