<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main test accounts
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        User::factory()->organizer()->create([
            'name' => 'Organizer User',
            'email' => 'organizer@example.com',
            'password' => 'password',
        ]);

        User::factory()->tabulator()->create([
            'name' => 'Tabulator User',
            'email' => 'tabulator@example.com',
            'password' => 'password',
        ]);

        User::factory()->judge()->create([
            'name' => 'Judge User',
            'email' => 'judge@example.com',
            'password' => 'password',
        ]);

        // Create additional users (optional)
        User::factory()->admin()->count(3)->create();
        User::factory()->organizer()->count(5)->create();
        User::factory()->tabulator()->count(5)->create();
        User::factory()->judge()->count(10)->create();
    }
}
