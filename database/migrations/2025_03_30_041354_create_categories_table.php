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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pageant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('weight')->default(100); // Percentage weight in overall scoring
            $table->decimal('max_score', 8, 2)->default(100.00);
            $table->string('scoring_type')->default('percentage'); // percentage, points, scale
            $table->integer('display_order')->default(0);
            $table->jsonb('criteria')->nullable(); // Sub-criteria if any
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
