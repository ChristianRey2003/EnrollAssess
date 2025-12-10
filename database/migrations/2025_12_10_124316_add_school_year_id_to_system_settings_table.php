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
        Schema::table('system_settings', function (Blueprint $table) {
            // Drop the unique constraint on 'key' to allow school-year-specific settings
            $table->dropUnique(['key']);
            
            // Add school_year_id column (nullable for backward compatibility)
            $table->unsignedBigInteger('school_year_id')->nullable()->after('id');
            $table->foreign('school_year_id')
                  ->references('school_year_id')
                  ->on('school_years')
                  ->onDelete('cascade');
            
            // Create composite unique index: key + school_year_id (null school_year_id = global setting)
            $table->unique(['key', 'school_year_id'], 'system_settings_key_school_year_unique');
            
            // Add index for school_year_id lookups
            $table->index('school_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            // Drop indexes and foreign key
            $table->dropForeign(['school_year_id']);
            $table->dropIndex(['school_year_id']);
            $table->dropUnique('system_settings_key_school_year_unique');
            
            // Remove school_year_id column
            $table->dropColumn('school_year_id');
            
            // Restore original unique constraint on 'key'
            $table->unique('key');
        });
    }
};
