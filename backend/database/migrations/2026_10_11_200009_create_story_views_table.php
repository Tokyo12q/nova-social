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
        Schema::create('story_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained('stories')->cascadeOnDelete();
            $table->foreignId('viewer_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('viewed_at')->useCurrent();

            $table->unique(['story_id', 'viewer_id'], 'idx_story_views_unique');
            $table->index('viewer_id', 'idx_story_views_viewer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_views');
    }
};
