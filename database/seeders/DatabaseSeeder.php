<?php

namespace Database\Seeders;

use Database\Seeders\Demo\DemoCrmSeeder;
use Database\Seeders\Demo\DemoUserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with reproducible demo data.
     */
    public function run(): void
    {
        $this->call([
            DemoUserSeeder::class,
            DemoCrmSeeder::class,
        ]);
    }
}
