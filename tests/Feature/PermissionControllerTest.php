<?php

namespace Tests\Feature;

use App\Models\Pageant;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the role permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_admin_can_access_user_management_with_permission(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.organizers'));

        $response->assertStatus(200);
    }

    public function test_organizer_can_create_contestant_with_permission(): void
    {
        $organizer = User::factory()->create([
            'role' => 'organizer',
            'is_active' => true,
            'is_verified' => true,
        ]);

        $pageant = Pageant::create([
            'name' => 'Test Pageant',
            'status' => 'Draft',
            'pageant_date' => now()->addDays(30),
            'scoring_system' => 'percentage',
            'contestant_type' => 'solo',
            'created_by' => $organizer->id,
        ]);

        // Assign organizer to pageant
        $pageant->organizers()->attach($organizer->id);

        // Organizer should have permission by default
        $this->assertTrue($organizer->hasPermission('organizer_create_contestant'));
    }

    public function test_organizer_cannot_create_contestant_without_permission(): void
    {
        // Revoke the permission
        RolePermission::where('role', 'organizer')
            ->where('permission_key', 'organizer_create_contestant')
            ->update(['granted' => false]);

        $organizer = User::factory()->create([
            'role' => 'organizer',
            'is_active' => true,
            'is_verified' => true,
        ]);

        $pageant = Pageant::create([
            'name' => 'Test Pageant',
            'status' => 'Draft',
            'pageant_date' => now()->addDays(30),
            'scoring_system' => 'percentage',
            'contestant_type' => 'solo',
            'created_by' => $organizer->id,
        ]);

        // Assign organizer to pageant
        $pageant->organizers()->attach($organizer->id);

        $response = $this->actingAs($organizer)
            ->post(route('organizer.pageant.contestants.bulk-store', $pageant->id), [
                'contestants' => [
                    [
                        'name' => 'Test Contestant',
                        'age' => 25,
                    ],
                ],
            ]);

        // Expect JSON error response for API calls
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You do not have permission to manage contestants.',
        ]);
    }

    public function test_judge_can_submit_scores_with_permission(): void
    {
        $judge = User::factory()->create([
            'role' => 'judge',
            'is_active' => true,
        ]);

        $this->assertTrue($judge->hasPermission('judge_submit_scores'));
    }

    public function test_judge_cannot_submit_scores_without_permission(): void
    {
        // Revoke the permission
        RolePermission::where('role', 'judge')
            ->where('permission_key', 'judge_submit_scores')
            ->update(['granted' => false]);

        $judge = User::factory()->create([
            'role' => 'judge',
            'is_active' => true,
        ]);

        $this->assertFalse($judge->hasPermission('judge_submit_scores'));
    }

    public function test_tabulator_can_view_judges_with_permission(): void
    {
        $tabulator = User::factory()->create([
            'role' => 'tabulator',
            'is_active' => true,
        ]);

        $this->assertTrue($tabulator->hasPermission('tabulator_view_judges'));
    }

    public function test_admin_permission_management_requires_grant_permission(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Admin should have this permission
        $this->assertTrue($admin->hasPermission('admin_grant_permissions'));

        $response = $this->actingAs($admin)->get(route('admin.users.permissions'));

        $response->assertStatus(200);
    }
}
