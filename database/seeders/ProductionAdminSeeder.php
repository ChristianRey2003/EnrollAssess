<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates a superadmin account for production use.
     * Usage: php artisan db:seed --class=ProductionAdminSeeder
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['username' => 'Administrator'],
            [
                'password_hash' => Hash::make('password!'),
                'full_name' => 'System Administrator',
                'role' => 'administrator',
                'email' => 'admin@evsu.edu.ph',
            ]
        );

        $this->command->info('✅ Superadmin user created/updated successfully!');
        $this->command->info('Username: Administrator');
        $this->command->info('Email: ' . $user->email);
        $this->command->info('Role: administrator (superadmin)');
        $this->command->info('Password: password!');
    }
}

