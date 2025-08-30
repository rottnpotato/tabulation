<?php

namespace App\Policies;

use App\Models\Pageant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PageantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'organizer';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pageant $pageant): bool
    {
        // Admins can view all pageants
        if ($user->role === 'admin') {
            return true;
        }

        // Organizers can view pageants they're assigned to
        if ($user->role === 'organizer') {
            return DB::table('pageant_organizers')
                ->where('user_id', $user->id)
                ->where('pageant_id', $pageant->id)
                ->exists();
        }

        // Judges can view pageants they're assigned to
        if ($user->role === 'judge') {
            return DB::table('pageant_judges')
                ->where('user_id', $user->id)
                ->where('pageant_id', $pageant->id)
                ->where('active', true)
                ->exists();
        }

        // Tabulators can view pageants they're assigned to
        if ($user->role === 'tabulator') {
            return DB::table('pageant_tabulators')
                ->where('user_id', $user->id)
                ->where('pageant_id', $pageant->id)
                ->where('active', true)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'organizer';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pageant $pageant): bool
    {
        // Admins can always update pageants
        if ($user->role === 'admin') {
            return true;
        }

        // Organizers can update pageants they're assigned to
        if ($user->role === 'organizer') {
            $hasAccess = DB::table('pageant_organizers')
                ->where('user_id', $user->id)
                ->where('pageant_id', $pageant->id)
                ->exists();

            if (! $hasAccess) {
                return false;
            }

            // Check if pageant can be edited based on status
            return $pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pageant $pageant): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pageant $pageant): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pageant $pageant): bool
    {
        return $user->role === 'admin';
    }
}
