<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One internal demo identity. Local test credentials only — documented, never
 * a real person, never a production account.
 *
 * Login: demo@dormed.test / password
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'demo@dormed.test'],
            [
                'name' => 'Demo Mitarbeiter',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
