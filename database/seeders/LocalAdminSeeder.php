<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LocalAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate a random strong password for the local admin
        $password = Str::random(16);

        $user = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'password_hash' => Hash::make($password),
                'full_name' => 'System Administrator',
                'role' => 'administrator',
                'email' => 'admin@example.test',
            ]
        );

        $this->command->info('Local administrator user created/updated for testing.');
        $this->command->info('Username: admin');
        $this->command->info('Generated Password: ' . $password);
        $this->command->info('Email: ' . $user->email);
    }
}


