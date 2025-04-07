<?php

namespace App\Console\Commands;

use App\Models\Contestant;
use Illuminate\Console\Command;

class ShowContestants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-contestants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all contestants in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $contestants = Contestant::all();
        
        if ($contestants->isEmpty()) {
            $this->error('No contestants found in the database.');
            return 1;
        }
        
        $this->info("{$contestants->count()} contestants found in the database:");

        $rows = $contestants->map(function ($contestant) {
            return [
                'ID' => $contestant->id,
                'Number' => $contestant->number,
                'Name' => $contestant->name,
                'Age' => $contestant->age,
                'Origin' => $contestant->origin,
                'Pageant ID' => $contestant->pageant_id,
            ];
        });

        $this->table(
            ['ID', 'Number', 'Name', 'Age', 'Origin', 'Pageant ID'],
            $rows
        );

        return 0;
    }
}
