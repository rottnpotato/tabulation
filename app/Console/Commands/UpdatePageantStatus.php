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
    protected $description = 'Automatically update pageant status based on start and end dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->startOfDay();
        $activatedCount = 0;
        $completedCount = 0;

        // Find pageants that should be set to Active (when start date is reached and end date not passed)
        $pageantsToActivate = Pageant::where('status', 'Draft')
            ->whereDate('start_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->get();

        foreach ($pageantsToActivate as $pageant) {
            $pageant->update(['status' => 'Active']);
            $activatedCount++;

            Log::info("Pageant '{$pageant->name}' (ID: {$pageant->id}) status automatically updated to Active.");
            $this->info("Activated: {$pageant->name}");
        }

        // Find pageants that should be set to Completed (when end date has passed)
        $pageantsToComplete = Pageant::whereIn('status', ['Active', 'Draft'])
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->get();

        foreach ($pageantsToComplete as $pageant) {
            $pageant->update(['status' => 'Completed']);
            $completedCount++;

            Log::info("Pageant '{$pageant->name}' (ID: {$pageant->id}) status automatically updated to Completed.");
            $this->info("Completed: {$pageant->name}");
        }

        if ($activatedCount === 0 && $completedCount === 0) {
            $this->info('No pageants need status update.');
        } else {
            if ($activatedCount > 0) {
                $this->info("Successfully activated {$activatedCount} pageant(s).");
            }
            if ($completedCount > 0) {
                $this->info("Successfully completed {$completedCount} pageant(s).");
            }
        }

        return self::SUCCESS;
    }
}
