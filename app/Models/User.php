<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Mail\OrganizerVerification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        'username',
        'role',
        'is_active',
        'email_verified_at',
        'verification_token',
        'notes'
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
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
     * Check if the user is a contestant
     *
     * @return bool
     */
    public function isContestant(): bool
    {
        return $this->hasRole('contestant');
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
     * Generate a verification token and send verification email
     *
     * @return void
     */
    public function sendVerificationEmail(): void
    {
        // Generate a new verification token
        $token = Str::random(64);
        $expiresAt = now()->addHours(24);
        
        // Save the token and expiration
        $this->update([
            'verification_token' => $token,
            'verification_expires_at' => $expiresAt,
        ]);
        
        // Send the verification email
        Mail::to($this->email)->send(new OrganizerVerification($this, $token));
    }
    
    /**
     * Get pageants that this user is an organizer for
     */
    public function pageants()
    {
        return $this->belongsToMany(Pageant::class, 'pageant_organizers', 'user_id', 'pageant_id');
    }

    /**
     * Get contestant profile associated with this user (if they are a contestant)
     */
    public function contestantProfile()
    {
        return $this->hasOne(Contestant::class, 'user_id');
    }

    /**
     * Get pageants this user participates in as a contestant
     */
    public function contestantPageants()
    {
        if ($this->isContestant() && $this->contestantProfile) {
            return collect([$this->contestantProfile->pageant]);
        }
        return collect([]);
    }
}
