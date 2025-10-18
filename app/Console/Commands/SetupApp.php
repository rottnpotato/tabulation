<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup {--fresh : Refresh the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the application with database migrations and seeders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up the application...');

        // Run migrations
        if ($this->option('fresh')) {
            $this->info('Refreshing the database...');
            Artisan::call('migrate:fresh', ['--seed' => true]);
            $this->info('Database refreshed and seeded successfully!');
        } else {
            $this->info('Running migrations...');
            Artisan::call('migrate');

            $this->info('Running seeders...');
            Artisan::call('db:seed');

            $this->info('Database migrated and seeded successfully!');
        }

        // Clear cache
        $this->info('Clearing cache...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // Link storage
        $this->info('Linking storage...');
        Artisan::call('storage:link');

        $this->info('Application setup completed successfully!');
        $this->info('You can now log in with the following test accounts:');
        $this->info('- Admin: admin@example.com / password');
        $this->info('- Organizer: organizer@example.com / password');
        $this->info('- Tabulator: tabulator@example.com / password');
        $this->info('- Judge: judge@example.com / password');

        return Command::SUCCESS;
    }
}
