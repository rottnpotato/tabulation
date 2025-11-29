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
        Schema::table('pageants', function (Blueprint $table) {
            // Ranking method determines how final rankings are calculated
            // 'score_average' - Traditional: highest weighted average wins
            // 'rank_sum' - Excel method: lowest sum of judge ranks wins
            $table->enum('ranking_method', ['score_average', 'rank_sum'])
                ->default('rank_sum')
                ->after('scoring_system')
                ->comment('Method for calculating final rankings');

            // Tie handling determines how tied scores/ranks are assigned
            // 'sequential' - Ties get consecutive ranks (1, 2, 2, 3)
            // 'average' - RANK.AVG style, ties share average (1, 2.5, 2.5, 4)
            // 'minimum' - RANK.MIN style, ties get same low rank (1, 2, 2, 4)
            $table->enum('tie_handling', ['sequential', 'average', 'minimum'])
                ->default('average')
                ->after('ranking_method')
                ->comment('How to handle tied scores when ranking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pageants', function (Blueprint $table) {
            $table->dropColumn(['ranking_method', 'tie_handling']);
        });
    }
};
