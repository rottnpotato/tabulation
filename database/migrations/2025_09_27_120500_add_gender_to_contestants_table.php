<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contestants', function (Blueprint $table): void {
            if (! Schema::hasColumn('contestants', 'gender')) {
                $table->string('gender', 10)->nullable()->after('age');
            }

            // Composite index for lookups and validations
            $table->index(['pageant_id', 'number', 'gender'], 'contestants_pageant_number_gender_index');
        });
    }

    public function down(): void
    {
        Schema::table('contestants', function (Blueprint $table): void {
            $table->dropIndex('contestants_pageant_number_gender_index');

            if (Schema::hasColumn('contestants', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }

    // No-op helper removed
};


