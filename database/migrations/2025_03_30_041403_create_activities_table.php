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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pageant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_type'); // CONTESTANT_ADDED, SCORE_UPDATED, EVENT_CREATED, etc.
            $table->string('entity_type')->nullable(); // Contestant, Judge, Category, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description');
            $table->string('icon')->nullable(); // For UI display
            $table->jsonb('metadata')->nullable(); // Additional details about the activity
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
