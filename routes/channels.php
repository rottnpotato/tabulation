<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Models\Pageant;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Global pageant channel - only admins can listen
Broadcast::channel('pageant.all', function ($user) {
    return $user->role === 'admin';
});

// Specific pageant channel - admins, or organizers/tabulators assigned to this pageant
Broadcast::channel('pageant.{id}', function ($user, $id) {
    // Admins can access all pageant channels
    if ($user->role === 'admin') {
        return true;
    }
    
    // Organizers and tabulators can only access their assigned pageants
    if ($user->role === 'organizer' || $user->role === 'tabulator') {
        $pageant = Pageant::find($id);
        if (!$pageant) {
            return false;
        }
        
        // Check if this user is assigned to the pageant
        return $pageant->organizers()->where('users.id', $user->id)->exists() ||
               $pageant->tabulators()->where('users.id', $user->id)->exists();
    }
    
    return false;
}); 