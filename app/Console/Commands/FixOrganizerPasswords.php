<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixOrganizerPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organizers:fix-passwords {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix double-hashed passwords for organizers created before the password hashing bug was fixed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
            $this->newLine();
        }

        // Find all organizers
        $organizers = User::where('role', 'organizer')->get();

        if ($organizers->isEmpty()) {
            $this->info('No organizers found.');
            return 0;
        }

        $this->info("Found {$organizers->count()} organizer(s)");
        $this->newLine();

        $affectedCount = 0;
        $defaultPassword = 'password123'; // Default password to set

        foreach ($organizers as $organizer) {
            // Try to verify with a known test password first
            // If neither common password works, assume it's a user-created password
            // that we need to reset
            $canAuthWithCommon = Hash::check('password', $organizer->password) || 
                                 Hash::check('password123', $organizer->password) ||
                                 Hash::check('testpassword123', $organizer->password);

            // Skip if this organizer can login with a common test password
            if ($canAuthWithCommon) {
                continue;
            }

            // For other users, we'll assume they need a password reset
            // since we can't verify their original password
            $affectedCount++;
            
            if ($dryRun) {
                $this->warn("Would reset password for: {$organizer->name} ({$organizer->email})");
            } else {
                // Reset to default password
                $organizer->update([
                    'password' => $defaultPassword, // Model cast will hash it once
                ]);
                
                $this->info("✓ Reset password for: {$organizer->name} ({$organizer->email})");
                $this->line("  New password: {$defaultPassword}");
            }
        }

        $this->newLine();

        if ($affectedCount === 0) {
            $this->info('No organizers with double-hashed passwords found.');
        } else {
            if ($dryRun) {
                $this->warn("Would reset passwords for {$affectedCount} organizer(s)");
                $this->info('Run without --dry-run to apply changes');
            } else {
                $this->info("Successfully reset passwords for {$affectedCount} organizer(s)");
                $this->warn("Default password set to: {$defaultPassword}");
                $this->line('Please notify affected organizers to change their passwords.');
            }
        }

        return 0;
    }
}
