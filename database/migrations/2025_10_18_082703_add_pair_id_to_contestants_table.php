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
        Schema::table('contestants', function (Blueprint $table) {
            // Add pair_id to link two contestants as a pair
            // Both members will have the same pair_id
            if (! Schema::hasColumn('contestants', 'pair_id')) {
                $table->uuid('pair_id')->nullable()->after('is_pair');
                $table->index('pair_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contestants', function (Blueprint $table) {
            if (Schema::hasColumn('contestants', 'pair_id')) {
                $table->dropIndex(['pair_id']);
                $table->dropColumn('pair_id');
            }
        });
    }
};
