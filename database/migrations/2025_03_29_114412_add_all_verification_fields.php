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
        Schema::table('users', function (Blueprint $table) {
            // Check if username column doesn't exist before adding it
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }

            // Check if is_verified column doesn't exist before adding it
            if (! Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('remember_token');
            }

            // Check if verification_token column doesn't exist before adding it
            if (! Schema::hasColumn('users', 'verification_token')) {
                $table->string('verification_token')->nullable()->after('is_verified');
            }

            // Check if verification_expires_at column doesn't exist before adding it
            if (! Schema::hasColumn('users', 'verification_expires_at')) {
                $table->timestamp('verification_expires_at')->nullable()->after('verification_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only drop columns if they exist
            $columnsToDrop = [];

            if (Schema::hasColumn('users', 'username')) {
                $columnsToDrop[] = 'username';
            }

            if (Schema::hasColumn('users', 'is_verified')) {
                $columnsToDrop[] = 'is_verified';
            }

            if (Schema::hasColumn('users', 'verification_token')) {
                $columnsToDrop[] = 'verification_token';
            }

            if (Schema::hasColumn('users', 'verification_expires_at')) {
                $columnsToDrop[] = 'verification_expires_at';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
