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
            $table->dateTime('interview_deadline_start')->nullable()->after('schedule_date');
            $table->dateTime('interview_deadline_end')->nullable()->after('interview_deadline_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropColumn(['interview_deadline_start', 'interview_deadline_end']);
        });
    }
};
