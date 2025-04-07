<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Pageant;
use App\Models\Criteria;
use App\Models\Contestant;
use Illuminate\Console\Command;

class ShowDatabaseSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:db-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display a summary of the database contents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Database Summary");
        $this->newLine();
        
        // Users
        $users = User::all();
        $admins = $users->where('role', 'admin')->count();
        $organizers = $users->where('role', 'organizer')->count();
        $tabulators = $users->where('role', 'tabulator')->count();
        $judges = $users->where('role', 'judge')->count();
        
        $this->info("USERS");
        $this->line("-----");
        $this->line("Total Users: {$users->count()}");
        $this->line("Admins: {$admins}");
        $this->line("Organizers: {$organizers}");
        $this->line("Tabulators: {$tabulators}");
        $this->line("Judges: {$judges}");
        $this->newLine();
        
        // Pageants
        $pageants = Pageant::all();
        $draftPageants = $pageants->where('status', 'Draft')->count();
        $setupPageants = $pageants->where('status', 'Setup')->count();
        $activePageants = $pageants->where('status', 'Active')->count();
        $completedPageants = $pageants->where('status', 'Completed')->count();
        $archivedPageants = $pageants->where('status', 'Archived')->count();
        $unlockedPageants = $pageants->where('status', 'Unlocked_For_Edit')->count();
        $cancelledPageants = $pageants->where('status', 'Cancelled')->count();
        
        $this->info("PAGEANTS");
        $this->line("--------");
        $this->line("Total Pageants: {$pageants->count()}");
        $this->line("Draft: {$draftPageants}");
        $this->line("Setup: {$setupPageants}");
        $this->line("Active: {$activePageants}");
        $this->line("Completed: {$completedPageants}");
        $this->line("Archived: {$archivedPageants}");
        $this->line("Unlocked For Edit: {$unlockedPageants}");
        $this->line("Cancelled: {$cancelledPageants}");
        $this->newLine();
        
        // Contestants
        $contestants = Contestant::all();
        $this->info("CONTESTANTS");
        $this->line("-----------");
        $this->line("Total Contestants: {$contestants->count()}");
        if ($contestants->isNotEmpty()) {
            $contestantsByPageant = $contestants->groupBy('pageant_id')->map->count();
            foreach ($contestantsByPageant as $pageantId => $count) {
                $pageantName = Pageant::find($pageantId)->name ?? "Pageant ID: {$pageantId}";
                $this->line("- {$pageantName}: {$count} contestants");
            }
        }
        $this->newLine();
        
        // Criteria
        $criteria = Criteria::all();
        $this->info("CRITERIA");
        $this->line("--------");
        $this->line("Total Criteria: {$criteria->count()}");
        if ($criteria->isNotEmpty()) {
            $criteriaByPageant = $criteria->groupBy('pageant_id')->map->count();
            foreach ($criteriaByPageant as $pageantId => $count) {
                $pageantName = Pageant::find($pageantId)->name ?? "Pageant ID: {$pageantId}";
                $this->line("- {$pageantName}: {$count} criteria");
            }
        }
        
        return 0;
    }
}
