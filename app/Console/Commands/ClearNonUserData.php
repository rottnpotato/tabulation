<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearNonUserData extends Command
{
    protected $signature = 'db:clear-non-user-data {--force : Force the operation without confirmation}';

    protected $description = 'Clear all database tables except users and related authentication tables';

    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete ALL data except users. Are you sure?')) {
                $this->info('Operation cancelled.');
                return self::FAILURE;
            }
        }

        $this->info('Starting database cleanup...');

        $preservedTables = [
            'users',
            'password_reset_tokens',
            'migrations',
            'cache',
            'cache_locks',
            'sessions',
            'failed_jobs',
            'jobs',
            'job_batches',
        ];

        $tablesToClear = [
            'scores',
            'activities',
            'audit_logs',
            'edit_access_requests',
            'contestant_images',
            'contestant_members',
            'contestants',
            'criteria',
            'pageant_judges',
            'pageant_organizers',
            'pageant_tabulators',
            'rounds',
            'categories',
            'segments',
            'pageants',
            'role_permissions',
        ];

        DB::beginTransaction();

        try {
            Schema::disableForeignKeyConstraints();

            foreach ($tablesToClear as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("✓ Cleared table: {$table}");
                }
            }

            Schema::enableForeignKeyConstraints();

            DB::commit();

            $this->info('');
            $this->info('✅ Database cleanup completed successfully!');
            $this->info('All data except users has been cleared.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();

            $this->error('❌ An error occurred: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
