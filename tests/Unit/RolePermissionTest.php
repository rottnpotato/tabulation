<?php

namespace Tests\Unit;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the role permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_can_retrieve_permissions_for_role(): void
    {
        $organizerPermissions = RolePermission::getPermissionsForRole('organizer');

        $this->assertNotEmpty($organizerPermissions);
        $this->assertTrue($organizerPermissions->count() > 0);
    }

    public function test_can_check_if_role_has_permission(): void
    {
        $hasPermission = RolePermission::hasPermission('organizer', 'organizer_edit_own_pageant');

        $this->assertTrue($hasPermission);
    }

    public function test_can_check_if_role_does_not_have_permission(): void
    {
        $hasPermission = RolePermission::hasPermission('tabulator', 'tabulator_edit_scores');

        $this->assertFalse($hasPermission);
    }

    public function test_user_can_check_permissions(): void
    {
        $user = User::factory()->create([
            'role' => 'organizer',
            'is_active' => true,
        ]);

        $this->assertTrue($user->hasPermission('organizer_edit_own_pageant'));
    }

    public function test_admin_always_has_all_permissions(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue($admin->hasPermission('any_permission_key'));
        $this->assertTrue($admin->hasPermission('non_existent_permission'));
    }

    public function test_can_update_role_permissions(): void
    {
        $permissions = [
            ['id' => 'tabulator_edit_scores', 'name' => 'Edit Submitted Scores', 'granted' => true],
        ];

        RolePermission::updateRolePermissions('tabulator', $permissions);

        $hasPermission = RolePermission::hasPermission('tabulator', 'tabulator_edit_scores');

        $this->assertTrue($hasPermission);
    }
}
