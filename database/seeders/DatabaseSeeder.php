<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\MenHaircutSeeder;
use Database\Seeders\WomanHaircutSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MenHaircutSeeder::class,
            WomanHaircutSeeder::class,
        ]);
    }
}
