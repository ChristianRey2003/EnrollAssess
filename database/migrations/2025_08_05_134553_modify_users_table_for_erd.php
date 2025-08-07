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
        Schema::table('users', function (Blueprint $table) {
            // Add new columns required by ERD
            $table->string('username')->unique()->after('id');
            $table->string('password_hash')->after('password');
            $table->string('full_name')->after('username');
            $table->enum('role', ['department-head', 'administrator', 'instructor'])->default('instructor')->after('full_name');
            
            // Modify existing columns
            $table->string('email')->nullable()->change();
            
            // Drop the old password column (we'll use password_hash)
            // Note: In production, you'd want to migrate data first
            $table->dropColumn('password');
            $table->dropColumn('name');
        });
        
        // Add an alias for user_id to maintain compatibility
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('user_id', 'id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'password_hash', 'full_name', 'role']);
            $table->string('name')->after('id');
            $table->string('password')->after('email_verified_at');
            $table->string('email')->nullable(false)->change();
        });
    }
};