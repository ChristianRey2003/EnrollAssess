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
            // Check if columns exist before adding them
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->after('id');
            }
            if (!Schema::hasColumn('users', 'password_hash')) {
                $table->string('password_hash')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name')->nullable()->after('username');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['department-head', 'administrator', 'instructor'])->default('instructor')->after('full_name');
            }
            
            // Modify existing columns
            $table->string('email')->nullable()->change();
        });
        
        // Drop columns in separate operation to avoid conflicts
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password') && Schema::hasColumn('users', 'password_hash')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
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