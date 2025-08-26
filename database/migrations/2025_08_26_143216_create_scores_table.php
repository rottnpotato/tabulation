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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pageant_id')->constrained()->onDelete('cascade');
            $table->foreignId('round_id')->constrained()->onDelete('cascade');
            $table->foreignId('criteria_id')->constrained('criteria')->onDelete('cascade');
            $table->foreignId('contestant_id')->constrained()->onDelete('cascade');
            $table->foreignId('judge_id')->constrained('users')->onDelete('cascade');
            $table->decimal('score', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            // Ensure one score per judge per contestant per criteria per round
            $table->unique(['pageant_id', 'round_id', 'criteria_id', 'contestant_id', 'judge_id'], 'unique_score_per_judge');
            
            // Add indexes for common queries
            $table->index(['pageant_id', 'round_id']);
            $table->index(['judge_id', 'pageant_id']);
            $table->index(['contestant_id', 'round_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
