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
            $table->string('archive_reason')->nullable()->after('status');
            $table->timestamp('archived_at')->nullable()->after('archive_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pageants', function (Blueprint $table) {
            $table->dropColumn(['archive_reason', 'archived_at']);
        });
    }
};
