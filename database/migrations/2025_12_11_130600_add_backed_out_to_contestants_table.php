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
            $table->boolean('backed_out')->default(false)->after('rank');
            $table->timestamp('backed_out_at')->nullable()->after('backed_out');
            $table->foreignId('backed_out_by')->nullable()->constrained('users')->nullOnDelete()->after('backed_out_at');
            $table->text('backed_out_reason')->nullable()->after('backed_out_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contestants', function (Blueprint $table) {
            $table->dropForeign(['backed_out_by']);
            $table->dropColumn(['backed_out', 'backed_out_at', 'backed_out_by', 'backed_out_reason']);
        });
    }
};
