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
        Schema::create('post_hashtags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('hashtag_id')->constrained('hashtags')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['post_id', 'hashtag_id'], 'idx_post_hashtags_unique');
            $table->index(['hashtag_id', 'post_id'], 'idx_post_hashtags_tag_post');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_hashtags');
    }
};
