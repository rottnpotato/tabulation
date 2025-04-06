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
        Schema::create('contestants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pageant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('number')->nullable();
            $table->string('origin')->nullable();
            $table->integer('age')->nullable();
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->jsonb('scores')->nullable(); // Store contestant scores in json format
            $table->jsonb('metadata')->nullable(); // Additional details like height, measurements, etc.
            $table->boolean('active')->default(true);
            $table->integer('rank')->nullable(); // Final rank in the pageant
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contestants');
    }
};
