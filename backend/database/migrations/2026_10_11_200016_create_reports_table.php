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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reportable_type');
            $table->unsignedBigInteger('reportable_id');
            $table->enum('reason', [
                'spam',
                'harassment',
                'hate_speech',
                'violence',
                'nudity',
                'misinformation',
                'other',
            ]);
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'in_review', 'resolved', 'dismissed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'idx_reports_status_created');
            $table->index(['reportable_type', 'reportable_id'], 'idx_reports_target');
            $table->index('reporter_id', 'idx_reports_reporter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
