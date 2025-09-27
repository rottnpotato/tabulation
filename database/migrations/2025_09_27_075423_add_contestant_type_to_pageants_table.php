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
            $table->enum('contestant_type', ['solo', 'pairs', 'both'])
                ->default('both')
                ->after('scoring_system')
                ->comment('Type of contestants allowed: solo only, pairs only, or both');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pageants', function (Blueprint $table) {
            $table->dropColumn('contestant_type');
        });
    }
};
