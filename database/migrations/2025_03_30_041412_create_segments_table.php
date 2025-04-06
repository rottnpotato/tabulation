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
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pageant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->string('type'); // swimwear, evening_gown, talent, qa, etc.
            $table->integer('weight')->default(100); // Percentage weight in overall scoring
            $table->decimal('max_score', 8, 2)->default(100.00);
            $table->string('scoring_type')->default('percentage'); // percentage, points, scale
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Cancelled'])->default('Pending');
            $table->integer('display_order')->default(0);
            $table->jsonb('rules')->nullable(); // Any specific rules for this segment
            $table->jsonb('scoring_criteria')->nullable(); // Specific criteria for this segment
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};
