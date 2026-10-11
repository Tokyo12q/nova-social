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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('caption')->nullable();
            $table->string('location', 255)->nullable();
            $table->boolean('comments_disabled')->default(false);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('saves_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at'], 'idx_posts_user_created');
            $table->index('created_at', 'idx_posts_created_at');
            $table->index('likes_count', 'idx_posts_likes_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
