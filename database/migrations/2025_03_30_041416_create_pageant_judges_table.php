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
        Schema::create('pageant_judges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pageant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('judge'); // judge, head_judge, guest_judge, etc.
            $table->jsonb('assigned_categories')->nullable(); // IDs of categories this judge is assigned to
            $table->jsonb('assigned_segments')->nullable(); // IDs of segments this judge is assigned to
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure a user can't be added as a judge to the same pageant multiple times
            $table->unique(['pageant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pageant_judges');
    }
};
