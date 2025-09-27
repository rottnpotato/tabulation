<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contestants', function (Blueprint $table): void {
            if (! Schema::hasColumn('contestants', 'is_pair')) {
                $table->boolean('is_pair')->default(false)->after('active');
            }
        });

        Schema::create('contestant_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pair_contestant_id');
            $table->unsignedBigInteger('member_contestant_id');
            $table->timestamps();

            $table->foreign('pair_contestant_id')
                ->references('id')->on('contestants')
                ->onDelete('cascade');

            $table->foreign('member_contestant_id')
                ->references('id')->on('contestants')
                ->onDelete('cascade');

            // A member can only belong to one pair (globally). Adjust later if multi-membership per pageant is desired.
            $table->unique(['member_contestant_id']);
            // Prevent duplicate member linkage within a pair
            $table->unique(['pair_contestant_id', 'member_contestant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contestant_members');

        Schema::table('contestants', function (Blueprint $table): void {
            if (Schema::hasColumn('contestants', 'is_pair')) {
                $table->dropColumn('is_pair');
            }
        });
    }
};
