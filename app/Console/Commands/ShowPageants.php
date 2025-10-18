<?php

namespace App\Console\Commands;

use App\Models\Pageant;
use Illuminate\Console\Command;

class ShowPageants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-pageants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all pageants in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pageants = Pageant::all();

        if ($pageants->isEmpty()) {
            $this->error('No pageants found in the database.');

            return 1;
        }

        $this->info("{$pageants->count()} pageants found in the database:");

        $rows = $pageants->map(function ($pageant) {
            return [
                'ID' => $pageant->id,
                'Name' => $pageant->name,
                'Status' => $pageant->status,
                'Start Date' => $pageant->start_date,
                'Progress' => "{$pageant->progress}%",
            ];
        });

        $this->table(
            ['ID', 'Name', 'Status', 'Start Date', 'Progress'],
            $rows
        );

        return 0;
    }
}
