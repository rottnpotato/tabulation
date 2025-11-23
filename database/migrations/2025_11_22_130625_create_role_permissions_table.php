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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // admin, organizer, tabulator, judge
            $table->string('permission_key'); // e.g., 'edit_own_pageant'
            $table->string('permission_name'); // Human-readable name
            $table->text('permission_description')->nullable();
            $table->boolean('granted')->default(false);
            $table->timestamps();

            // Ensure unique role-permission combinations
            $table->unique(['role', 'permission_key']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
