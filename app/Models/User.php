<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'username',
        'is_verified',
        'verification_token',
        'verification_expires_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verification_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Check if the user has a specific role
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user is an admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user is an organizer
     *
     * @return bool
     */
    public function isOrganizer(): bool
    {
        return $this->hasRole('organizer');
    }

    /**
     * Check if the user is a tabulator
     *
     * @return bool
     */
    public function isTabulator(): bool
    {
        return $this->hasRole('tabulator');
    }

    /**
     * Check if the user is a judge
     *
     * @return bool
     */
    public function isJudge(): bool
    {
        return $this->hasRole('judge');
    }
    
    /**
     * Check if the user's email is verified
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }
    
    /**
     * Check if the user's verification token is expired
     *
     * @return bool
     */
    public function isVerificationExpired(): bool
    {
        if (!$this->verification_expires_at) {
            return false;
        }
        
        return now()->gt($this->verification_expires_at);
    }
    
    /**
     * Get pageants that this user is an organizer for
     */
    public function pageants()
    {
        return $this->belongsToMany(Pageant::class, 'pageant_organizers', 'user_id', 'pageant_id');
    }
}
