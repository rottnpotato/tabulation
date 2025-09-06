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
        Schema::table('scores', function (Blueprint $table) {
            // Index for aggregation queries (judge-round-contestant lookups)
            $table->index(['judge_id', 'round_id', 'contestant_id'], 'idx_scores_judge_round_contestant');

            // Index for pageant-round-criteria lookups
            $table->index(['pageant_id', 'round_id', 'criteria_id'], 'idx_scores_pageant_round_criteria');

            // Index for contestant score aggregation across rounds
            $table->index(['contestant_id', 'pageant_id'], 'idx_scores_contestant_pageant');

            // Index for judge progress tracking
            $table->index(['judge_id', 'pageant_id', 'round_id'], 'idx_scores_judge_progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropIndex('idx_scores_judge_round_contestant');
            $table->dropIndex('idx_scores_pageant_round_criteria');
            $table->dropIndex('idx_scores_contestant_pageant');
            $table->dropIndex('idx_scores_judge_progress');
        });
    }
};
