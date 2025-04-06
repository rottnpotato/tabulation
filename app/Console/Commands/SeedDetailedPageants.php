<?php

namespace App\Console\Commands;

use Database\Seeders\DetailedPageantSeeder;
use Illuminate\Console\Command;

class SeedDetailedPageants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:detailed-pageants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with detailed pageant data for the PageantDetails component';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding detailed pageant data...');
        $this->call('db:seed', [
            '--class' => 'Database\Seeders\DetailedPageantSeeder',
        ]);
        $this->info('Detailed pageants seeded successfully!');
        $this->info('- Created "Miss Universe 2025" pageant with complete data');
        $this->info('- Created "Miss World 2025" pageant in setup state');
        $this->info('You can now view these at /admin/pageants');
        
        return Command::SUCCESS;
    }
} 