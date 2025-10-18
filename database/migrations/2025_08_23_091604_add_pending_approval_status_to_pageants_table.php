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
            // Update the status enum to include Pending_Approval
            $table->dropColumn('status');
        });

        Schema::table('pageants', function (Blueprint $table) {
            $table->enum('status', [
                'Pending_Approval',
                'Draft',
                'Setup',
                'Active',
                'Completed',
                'Unlocked_For_Edit',
                'Archived',
                'Cancelled',
            ])->default('Pending_Approval')->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pageants', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('pageants', function (Blueprint $table) {
            $table->enum('status', [
                'Draft',
                'Setup',
                'Active',
                'Completed',
                'Unlocked_For_Edit',
                'Archived',
                'Cancelled',
            ])->default('Draft')->after('location');
        });
    }
};
