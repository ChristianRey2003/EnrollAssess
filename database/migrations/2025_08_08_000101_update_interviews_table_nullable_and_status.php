<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            // Allow null schedule_date for assigned-but-not-scheduled interviews
            $table->dateTime('schedule_date')->nullable()->change();

            // Add 'assigned' status to support pre-scheduling assignments
            $table->enum('status', ['assigned', 'scheduled', 'completed', 'cancelled', 'rescheduled'])
                ->default('assigned')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            // Revert schedule_date to not nullable
            $table->dateTime('schedule_date')->nullable(false)->change();

            // Revert enum to original set (without 'assigned')
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])
                ->default('scheduled')
                ->change();
        });
    }
};


