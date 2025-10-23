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
        Schema::table('interviews', function (Blueprint $table) {
            // Add claimed columns for interview pool management
            $table->foreignId('claimed_by')->nullable()->constrained('users', 'user_id')->onDelete('set null')->after('interviewer_id');
            $table->timestamp('claimed_at')->nullable()->after('claimed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropForeign(['claimed_by']);
            $table->dropColumn(['claimed_by', 'claimed_at']);
        });
    }
};