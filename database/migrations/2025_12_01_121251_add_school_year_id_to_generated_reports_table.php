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
        Schema::table('generated_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('generated_reports', 'school_year_id')) {
                // Use explicit column + FK so we can reference the non-standard PK name `school_year_id`
                $table->unsignedBigInteger('school_year_id')->nullable()->after('id');
                $table->foreign('school_year_id')
                      ->references('school_year_id')
                      ->on('school_years')
                      ->nullOnDelete();
                $table->index('school_year_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_reports', function (Blueprint $table) {
            if (Schema::hasColumn('generated_reports', 'school_year_id')) {
                // Drop FK + index before dropping the column
                $table->dropForeign(['school_year_id']);
                $table->dropIndex(['school_year_id']);
                $table->dropColumn('school_year_id');
            }
        });
    }
};
