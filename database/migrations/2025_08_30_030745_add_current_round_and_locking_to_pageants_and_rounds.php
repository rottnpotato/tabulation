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
        // Add current_round_id to pageants table
        Schema::table('pageants', function (Blueprint $table) {
            $table->unsignedBigInteger('current_round_id')->nullable()->after('locked_by');
            $table->foreign('current_round_id')->references('id')->on('rounds')->onDelete('set null');
        });

        // Add locking fields to rounds table
        Schema::table('rounds', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('scoring_config');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');

            $table->foreign('locked_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropColumn(['is_locked', 'locked_at', 'locked_by']);
        });

        Schema::table('pageants', function (Blueprint $table) {
            $table->dropForeign(['current_round_id']);
            $table->dropColumn('current_round_id');
        });
    }
};
