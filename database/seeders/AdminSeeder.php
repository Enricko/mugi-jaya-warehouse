<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the initial Owner account.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'owner@mugijaya.com'],
            [
                'password_hash' => Hash::make('password'),
                'full_name' => 'Pak Sukma',
                'phone' => '08123456789',
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        $this->command->info('Owner account seeded: owner@mugijaya.com / password');
    }
}
