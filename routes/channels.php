<?php

use App\Models\Pageant;
use Illuminate\Support\Facades\Broadcast;

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

// Admin notifications channel - only admins can listen
Broadcast::channel('admin-notifications', function ($user) {
    return $user->role === 'admin';
});

// Global pageant channel - only admins can listen
Broadcast::channel('pageant.all', function ($user) {
    return $user->role === 'admin';
});

// Specific pageant channel - admins, or organizers/tabulators assigned to this pageant
Broadcast::channel('pageant.{id}', function ($user, $id) {
    if ($user->role === 'admin') {
        return true;
    }

    $pageant = Pageant::find($id);
    if (! $pageant) {
        return false;
    }

    if ($user->role === 'organizer') {
        return $pageant->organizers()->where('users.id', $user->id)->exists();
    }

    if ($user->role === 'tabulator') {
        return $pageant->tabulators()->where('users.id', $user->id)->exists();
    }

    if ($user->role === 'judge') {
        return $pageant->judges()->where('users.id', $user->id)->exists();
    }

    return false;
});

// Organizer-specific pageant activity channel
Broadcast::channel('organizer.pageant.{id}', function ($user, $id) {
    if ($user->role === 'admin') {
        return true;
    }

    if ($user->role !== 'organizer') {
        return false;
    }

    $pageant = Pageant::find($id);
    if (! $pageant) {
        return false;
    }

    return $pageant->organizers()->where('users.id', $user->id)->exists();
});

// Judge-specific notification channel
Broadcast::channel('judge.{id}', function ($user, $id) {
    // Only the judge themselves or admins can listen to judge notifications
    return $user->role === 'admin' || ((int) $user->id === (int) $id && $user->role === 'judge');
});
