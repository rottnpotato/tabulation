<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ShowUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all users in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->error('No users found in the database.');

            return 1;
        }

        $this->info("{$users->count()} users found in the database:");

        $rows = $users->map(function ($user) {
            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Role' => $user->role,
                'Active' => $user->is_active ? 'Yes' : 'No',
                'Verified' => $user->email_verified_at ? 'Yes' : 'No',
            ];
        });

        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Active', 'Verified'],
            $rows
        );

        return 0;
    }
}
