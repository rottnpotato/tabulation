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
        // Add pageant_date to pageants table
        Schema::table('pageants', function (Blueprint $table) {
            $table->date('pageant_date')->nullable()->after('end_date');
        });

        // Drop events table
        Schema::dropIfExists('events');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate events table
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pageant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // setup, contestant_registration, scoring_setup, photoshoot, etc.
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->string('venue')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Cancelled'])->default('Pending');
            $table->jsonb('metadata')->nullable(); // Additional details specific to event type
            $table->boolean('is_milestone')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Remove pageant_date from pageants table
        Schema::table('pageants', function (Blueprint $table) {
            $table->dropColumn('pageant_date');
        });
    }
};