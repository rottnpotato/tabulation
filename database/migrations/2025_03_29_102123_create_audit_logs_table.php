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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(); // NULL = system action
            $table->string('user_role')->nullable();
            $table->string('action_type'); // e.g., 'GRANT_EDIT_PERMISSION'
            $table->string('target_entity')->nullable(); // e.g., 'Pageant'
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('details');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            // Create index for faster querying
            $table->index(['user_id', 'action_type', 'target_entity', 'target_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
