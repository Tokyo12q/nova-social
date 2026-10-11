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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('media_type', ['image', 'video']);
            $table->string('media_url', 2048);
            $table->string('caption', 500)->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'expires_at'], 'idx_stories_user_expires');
            $table->index('expires_at', 'idx_stories_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
