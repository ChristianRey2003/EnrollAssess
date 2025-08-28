<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'username' => 'dept_head',
                'password_hash' => Hash::make('password'),
                'full_name' => 'Dr. Maria Elena Santos',
                'role' => 'department-head',
                'email' => 'maria.santos@evsu.edu.ph',
            ],

            [
                'username' => 'instructor1',
                'password_hash' => Hash::make('password'),
                'full_name' => 'Prof. Michael Angelo Garcia',
                'role' => 'instructor',
                'email' => 'michael.garcia@evsu.edu.ph',
            ],
            [
                'username' => 'instructor2',
                'password_hash' => Hash::make('password'),
                'full_name' => 'Prof. Lisa Marie Torres',
                'role' => 'instructor',
                'email' => 'lisa.torres@evsu.edu.ph',
            ],
            [
                'username' => 'instructor3',
                'password_hash' => Hash::make('password'),
                'full_name' => 'Prof. Robert James Villanueva',
                'role' => 'instructor',
                'email' => 'robert.villanueva@evsu.edu.ph',
            ],
            [
                'username' => 'instructor4',
                'password_hash' => Hash::make('password'),
                'full_name' => 'Prof. Sarah Jane Mendoza',
                'role' => 'instructor',
                'email' => 'sarah.mendoza@evsu.edu.ph',
            ],
            [
                'username' => 'instructor5',
                'password_hash' => Hash::make('password'),
                'full_name' => 'Prof. Christopher Paul Ramos',
                'role' => 'instructor',
                'email' => 'christopher.ramos@evsu.edu.ph',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                $userData
            );
        }

        $this->command->info('Users seeded successfully!');
    }
}