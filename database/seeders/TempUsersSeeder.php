<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TempUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin (department-head)
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'department-head',
                'status' => 'active',
            ]
        );

        // Instructor
        User::updateOrCreate(
            ['email' => 'instructor@example.com'],
            [
                'name' => 'Instructor User',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'status' => 'active',
            ]
        );
    }
}
