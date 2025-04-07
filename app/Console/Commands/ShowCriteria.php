<?php

namespace App\Console\Commands;

use App\Models\Criteria;
use Illuminate\Console\Command;

class ShowCriteria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-criteria';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all criteria in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $criteria = Criteria::all();
        
        if ($criteria->isEmpty()) {
            $this->error('No criteria found in the database.');
            return 1;
        }
        
        $this->info("{$criteria->count()} criteria found in the database:");

        $rows = $criteria->map(function ($criterion) {
            return [
                'ID' => $criterion->id,
                'Name' => $criterion->name,
                'Weight' => $criterion->weight,
                'Min Score' => $criterion->min_score,
                'Max Score' => $criterion->max_score,
                'Pageant ID' => $criterion->pageant_id,
                'Segment ID' => $criterion->segment_id ?? 'None',
            ];
        });

        $this->table(
            ['ID', 'Name', 'Weight', 'Min Score', 'Max Score', 'Pageant ID', 'Segment ID'],
            $rows
        );

        return 0;
    }
}
