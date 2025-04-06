<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an action in the audit log
     *
     * @param string $action The action type (e.g., "PAGEANT_CREATED", "PERMISSION_GRANTED")
     * @param string $entityType The type of entity being affected (e.g., "Pageant", "User")
     * @param int|string $entityId The ID of the entity being affected
     * @param string $details Human-readable details about the action
     * @return AuditLog The created audit log entry
     */
    public function log(string $action, string $entityType, $entityId, string $details): AuditLog
    {
        $user = Auth::user();
        
        return AuditLog::create([
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : 'SYSTEM',
            'action_type' => $action,
            'target_entity' => $entityType,
            'target_id' => $entityId,
            'details' => $details,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log a system action to the audit log.
     *
     * @param string $actionType The type of action being performed
     * @param string|null $targetEntity The entity being affected (e.g., 'Pageant')
     * @param int|null $targetId The ID of the entity being affected
     * @param string $details Human-readable details about the action
     * @return AuditLog The created audit log entry
     */
    public function logSystemAction(
        string $actionType,
        ?string $targetEntity = null,
        ?int $targetId = null,
        string $details = ''
    ): AuditLog {
        return AuditLog::create([
            'user_id' => null,
            'user_role' => 'SYSTEM',
            'action_type' => $actionType,
            'target_entity' => $targetEntity,
            'target_id' => $targetId,
            'details' => $details,
            'ip_address' => request()->ip(),
        ]);
    }
} 