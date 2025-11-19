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
            $table->boolean('is_temporarily_editable')->default(false)->after('is_locked');
            $table->foreignId('temporary_edit_granted_by')->nullable()->constrained('users')->onDelete('set null')->after('is_temporarily_editable');
            $table->timestamp('temporary_edit_granted_at')->nullable()->after('temporary_edit_granted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pageants', function (Blueprint $table) {
            $table->dropForeign(['temporary_edit_granted_by']);
            $table->dropColumn(['is_temporarily_editable', 'temporary_edit_granted_by', 'temporary_edit_granted_at']);
        });
    }
};
