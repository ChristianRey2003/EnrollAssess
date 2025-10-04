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
            // Add interview pool support fields
            $table->enum('pool_status', ['available', 'claimed', 'assigned', 'completed'])->default('available')->after('status');
            $table->foreignId('claimed_by')->nullable()->constrained('users', 'user_id')->onDelete('set null')->after('interviewer_id');
            $table->timestamp('claimed_at')->nullable()->after('claimed_by');
            $table->enum('priority_level', ['high', 'medium', 'low'])->default('medium')->after('claimed_at');
            $table->boolean('dh_override')->default(false)->after('priority_level');
            $table->text('assignment_notes')->nullable()->after('dh_override');
            
            // Update status enum to include pool-related statuses
            $table->enum('status', ['available', 'claimed', 'assigned', 'scheduled', 'completed', 'cancelled', 'rescheduled'])
                ->default('available')
                ->change();
            
            // Make interviewer_id nullable since interviews can start without assigned interviewer
            $table->foreignId('interviewer_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            // Remove interview pool support fields
            $table->dropColumn([
                'pool_status',
                'claimed_by',
                'claimed_at',
                'priority_level',
                'dh_override',
                'assignment_notes'
            ]);
            
            // Revert status enum to original
            $table->enum('status', ['assigned', 'scheduled', 'completed', 'cancelled', 'rescheduled'])
                ->default('assigned')
                ->change();
                
            // Make interviewer_id not nullable again
            $table->foreignId('interviewer_id')->nullable(false)->change();
        });
    }
};