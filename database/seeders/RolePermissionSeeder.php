<?php

namespace Database\Seeders;

use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin permissions - full access by default
        $adminPermissions = [
            ['permission_key' => 'admin_create_pageant', 'permission_name' => 'Create Pageants', 'granted' => true],
            ['permission_key' => 'admin_edit_pageant', 'permission_name' => 'Edit Pageants', 'granted' => true],
            ['permission_key' => 'admin_delete_pageant', 'permission_name' => 'Delete Pageants', 'granted' => true],
            ['permission_key' => 'admin_manage_users', 'permission_name' => 'Manage All Users', 'granted' => true],
            ['permission_key' => 'admin_view_audit_log', 'permission_name' => 'View Audit Logs', 'granted' => true],
            ['permission_key' => 'admin_system_settings', 'permission_name' => 'Configure System Settings', 'granted' => true],
            ['permission_key' => 'admin_grant_permissions', 'permission_name' => 'Grant/Revoke Permissions', 'granted' => true],
            ['permission_key' => 'admin_view_reports', 'permission_name' => 'Access All Reports', 'granted' => true],
        ];

        // Organizer permissions
        $organizerPermissions = [
            ['permission_key' => 'organizer_edit_own_pageant', 'permission_name' => 'Edit Own Pageants', 'granted' => true],
            ['permission_key' => 'organizer_create_contestant', 'permission_name' => 'Create & Edit Contestants', 'granted' => true],
            ['permission_key' => 'organizer_manage_judges', 'permission_name' => 'Assign Judges', 'granted' => true],
            ['permission_key' => 'organizer_manage_criteria', 'permission_name' => 'Configure Criteria & Scoring', 'granted' => true],
            ['permission_key' => 'organizer_view_results', 'permission_name' => 'View Results & Reports', 'granted' => true],
            ['permission_key' => 'organizer_publish_results', 'permission_name' => 'Publish Final Results', 'granted' => true],
            ['permission_key' => 'organizer_export_data', 'permission_name' => 'Export Pageant Data', 'granted' => true],
            ['permission_key' => 'organizer_assign_tabulators', 'permission_name' => 'Assign Tabulators', 'granted' => true],
        ];

        // Tabulator permissions
        $tabulatorPermissions = [
            ['permission_key' => 'tabulator_view_judges', 'permission_name' => 'View Judge Information', 'granted' => true],
            ['permission_key' => 'tabulator_view_scores', 'permission_name' => 'View Individual Scores', 'granted' => true],
            ['permission_key' => 'tabulator_tabulate_results', 'permission_name' => 'Tabulate & Verify Results', 'granted' => true],
            ['permission_key' => 'tabulator_print_reports', 'permission_name' => 'Generate Score Reports', 'granted' => true],
            ['permission_key' => 'tabulator_view_contestants', 'permission_name' => 'View Contestant Details', 'granted' => true],
            ['permission_key' => 'tabulator_edit_scores', 'permission_name' => 'Edit Submitted Scores', 'granted' => false],
            ['permission_key' => 'tabulator_export_data', 'permission_name' => 'Export Score Data', 'granted' => true],
            ['permission_key' => 'tabulator_publish_results', 'permission_name' => 'Publish Results', 'granted' => false],
        ];

        // Judge permissions
        $judgePermissions = [
            ['permission_key' => 'judge_view_criteria', 'permission_name' => 'View Scoring Criteria', 'granted' => true],
            ['permission_key' => 'judge_submit_scores', 'permission_name' => 'Submit Scores', 'granted' => true],
            ['permission_key' => 'judge_edit_own_scores', 'permission_name' => 'Edit Own Submitted Scores', 'granted' => true],
            ['permission_key' => 'judge_view_contestants', 'permission_name' => 'View Contestant Profiles', 'granted' => true],
            ['permission_key' => 'judge_view_other_judges', 'permission_name' => 'View Other Judges Profiles', 'granted' => false],
            ['permission_key' => 'judge_view_results', 'permission_name' => 'View Results', 'granted' => false],
            ['permission_key' => 'judge_export_scores', 'permission_name' => 'Export Own Scores', 'granted' => false],
        ];

        // Insert admin permissions
        foreach ($adminPermissions as $perm) {
            RolePermission::updateOrCreate(
                ['role' => 'admin', 'permission_key' => $perm['permission_key']],
                [
                    'permission_name' => $perm['permission_name'],
                    'granted' => $perm['granted'],
                ]
            );
        }

        // Insert organizer permissions
        foreach ($organizerPermissions as $perm) {
            RolePermission::updateOrCreate(
                ['role' => 'organizer', 'permission_key' => $perm['permission_key']],
                [
                    'permission_name' => $perm['permission_name'],
                    'granted' => $perm['granted'],
                ]
            );
        }

        // Insert tabulator permissions
        foreach ($tabulatorPermissions as $perm) {
            RolePermission::updateOrCreate(
                ['role' => 'tabulator', 'permission_key' => $perm['permission_key']],
                [
                    'permission_name' => $perm['permission_name'],
                    'granted' => $perm['granted'],
                ]
            );
        }

        // Insert judge permissions
        foreach ($judgePermissions as $perm) {
            RolePermission::updateOrCreate(
                ['role' => 'judge', 'permission_key' => $perm['permission_key']],
                [
                    'permission_name' => $perm['permission_name'],
                    'granted' => $perm['granted'],
                ]
            );
        }
    }
}
