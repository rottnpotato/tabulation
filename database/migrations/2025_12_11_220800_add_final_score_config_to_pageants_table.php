<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds final round scoring configuration fields to pageants table:
     * - final_score_mode: 'fresh' (reset to zero) or 'inherit' (carry over from previous stages)
     * - final_score_inheritance: JSON config for percentage inheritance from each stage type
     */
    public function up(): void
    {
        Schema::table('pageants', function (Blueprint $table) {
            // Mode: 'fresh' = start from zero, 'inherit' = carry scores from previous stages
            $table->string('final_score_mode')->default('fresh')->after('tie_handling');
            
            // JSON configuration for inheritance percentages per stage type
            // Example: {"semi-final": 30, "final": 70} means 30% from semi-final, 70% from final round
            $table->jsonb('final_score_inheritance')->nullable()->after('final_score_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pageants', function (Blueprint $table) {
            $table->dropColumn(['final_score_mode', 'final_score_inheritance']);
        });
    }
};
