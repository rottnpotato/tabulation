<?php

namespace App\Policies;

use App\Models\Criteria;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CriteriaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'organizer', 'judge', 'tabulator']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Criteria $criteria): bool
    {
        return in_array($user->role, ['admin', 'organizer', 'judge', 'tabulator']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'organizer']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Criteria $criteria): bool
    {
        $pageant = $criteria->pageant;

        // Admins can always update
        if ($user->role === 'admin') {
            return true;
        }

        // Organizers can update if pageant is editable
        if ($user->role === 'organizer') {
            $hasAccess = DB::table('pageant_organizers')
                ->where('user_id', $user->id)
                ->where('pageant_id', $pageant->id)
                ->exists();

            if (! $hasAccess) {
                return false;
            }

            // If pageant is ongoing, only allow edit with temporary access
            if ($pageant->isOngoing()) {
                return $pageant->is_temporarily_editable === true;
            }

            return $pageant->canBeEdited();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Criteria $criteria): bool
    {
        // Deleting is completely locked for ongoing pageants
        $pageant = $criteria->pageant;

        if ($pageant->isOngoing()) {
            return false;
        }

        // Only admins can delete
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Criteria $criteria): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Criteria $criteria): bool
    {
        return $user->role === 'admin';
    }
}
