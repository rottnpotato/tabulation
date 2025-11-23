<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call our UserSeeder to create test accounts
        $this->call([
            UserSeeder::class,
            PageantSeeder::class,
            DetailedPageantSeeder::class,
            CriteriaSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
