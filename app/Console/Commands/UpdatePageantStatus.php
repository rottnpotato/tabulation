<?php

namespace App\Console\Commands;

use App\Models\Pageant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdatePageantStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pageants:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update pageant status to Ongoing when start date is reached';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->startOfDay();

        // Find pageants that should be set to Active (when start date is reached)
        $pageants = Pageant::where('status', 'Draft')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        if ($pageants->isEmpty()) {
            $this->info('No pageants need status update.');

            return self::SUCCESS;
        }

        $updatedCount = 0;

        foreach ($pageants as $pageant) {
            $pageant->update(['status' => 'Active']);
            $updatedCount++;

            Log::info("Pageant '{$pageant->name}' (ID: {$pageant->id}) status automatically updated to Active.");
            $this->info("Updated: {$pageant->name}");
        }

        $this->info("Successfully updated {$updatedCount} pageant(s) to Active status.");

        return self::SUCCESS;
    }
}
