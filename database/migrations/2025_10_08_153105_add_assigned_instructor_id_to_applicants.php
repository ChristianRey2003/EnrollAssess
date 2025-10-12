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
        Schema::table('applicants', function (Blueprint $table) {
            if (!Schema::hasColumn('applicants', 'assigned_instructor_id')) {
                $table->unsignedBigInteger('assigned_instructor_id')->nullable()->after('phone_number');
                $table->foreign('assigned_instructor_id')->references('user_id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            if (Schema::hasColumn('applicants', 'assigned_instructor_id')) {
                $table->dropForeign(['assigned_instructor_id']);
                $table->dropColumn('assigned_instructor_id');
            }
        });
    }
};
