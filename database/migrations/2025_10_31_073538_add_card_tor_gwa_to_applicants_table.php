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
            // Add CARD/TOR GWA field - stored once per applicant as percentage (0-100)
            $table->decimal('card_tor_gwa', 5, 2)->nullable()->after('interview_score')
                  ->comment('CARD/TOR GWA as percentage (0-100), collected during interview');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('card_tor_gwa');
        });
    }
};
