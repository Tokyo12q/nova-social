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
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted'])->default('accepted');
            $table->timestamps();

            $table->unique(['follower_id', 'following_id'], 'idx_follows_pair');
            $table->index(['following_id', 'status'], 'idx_follows_following_status');
            $table->index(['follower_id', 'status'], 'idx_follows_follower_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
