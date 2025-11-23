<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'permission_key',
        'permission_name',
        'permission_description',
        'granted',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
        ];
    }

    /**
     * Get all permissions for a specific role
     */
    public static function getPermissionsForRole(string $role): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('role', $role)->get();
    }

    /**
     * Check if a role has a specific permission
     */
    public static function hasPermission(string $role, string $permissionKey): bool
    {
        try {
            return self::where('role', $role)
                ->where('permission_key', $permissionKey)
                ->where('granted', true)
                ->exists();
        } catch (\Exception $e) {
            // If permissions table doesn't exist or there's a DB error,
            // fail gracefully by denying permission
            \Log::warning("Permission check failed for role {$role}, permission {$permissionKey}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update permissions for a role
     */
    public static function updateRolePermissions(string $role, array $permissions): void
    {
        foreach ($permissions as $permission) {
            self::updateOrCreate(
                [
                    'role' => $role,
                    'permission_key' => $permission['id'],
                ],
                [
                    'permission_name' => $permission['name'],
                    'granted' => $permission['granted'],
                ]
            );
        }
    }
}
