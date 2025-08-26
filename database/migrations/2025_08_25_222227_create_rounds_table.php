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
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pageant_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Evening Gown, Production Number, Q&A, etc.
            $table->text('description')->nullable();
            $table->string('type')->default('competition'); // competition, preliminary, final
            $table->integer('weight')->default(100); // Weight percentage for scoring
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('scoring_config')->nullable(); // Additional scoring configuration
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
