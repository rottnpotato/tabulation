<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'ordinal' to the ranking_method enum for the pageants table.
     * The ordinal method implements the Final Ballot system used in major pageants:
     * - Judges give ranks (1st, 2nd, 3rd) instead of scores for final determination
     * - Primary victory: Majority of "Rank 1" votes wins automatically
     * - Secondary victory: Sum of ranks (golf system) - lowest total wins
     */
    public function up(): void
    {
        // For PostgreSQL, we need to add the new value to the enum type
        // First check if the value already exists to make migration idempotent
        $enumValues = DB::select("
            SELECT enumlabel 
            FROM pg_enum 
            WHERE enumtypid = (
                SELECT oid FROM pg_type WHERE typname = 'pageants_ranking_method_check'
            )
        ");

        $existingValues = array_map(fn ($e) => $e->enumlabel, $enumValues);

        if (! in_array('ordinal', $existingValues)) {
            // Drop the existing check constraint
            DB::statement('ALTER TABLE pageants DROP CONSTRAINT IF EXISTS pageants_ranking_method_check');

            // Add new check constraint with ordinal included
            DB::statement("
                ALTER TABLE pageants 
                ADD CONSTRAINT pageants_ranking_method_check 
                CHECK (ranking_method::text = ANY (ARRAY['score_average'::character varying, 'rank_sum'::character varying, 'ordinal'::character varying]::text[]))
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any ordinal values back to rank_sum (closest equivalent)
        DB::table('pageants')
            ->where('ranking_method', 'ordinal')
            ->update(['ranking_method' => 'rank_sum']);

        // Drop the constraint and recreate without ordinal
        DB::statement('ALTER TABLE pageants DROP CONSTRAINT IF EXISTS pageants_ranking_method_check');

        DB::statement("
            ALTER TABLE pageants 
            ADD CONSTRAINT pageants_ranking_method_check 
            CHECK (ranking_method::text = ANY (ARRAY['score_average'::character varying, 'rank_sum'::character varying]::text[]))
        ");
    }
};
