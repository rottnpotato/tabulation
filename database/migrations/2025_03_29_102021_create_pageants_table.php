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
        Schema::create('pageants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('venue')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', [
                'Draft',
                'Setup',
                'Active',
                'Completed',
                'Unlocked_For_Edit',
                'Archived',
                'Cancelled',
            ])->default('Draft');
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_edit_permission_granted')->default(false);
            $table->dateTime('edit_permission_expires_at')->nullable();
            $table->foreignId('edit_permission_granted_to')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pageants');
    }
};
