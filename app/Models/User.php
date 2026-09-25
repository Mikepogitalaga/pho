<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Program;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'address',
        'role',
        'is_active',
        'program_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * Determine if the user has administrator privileges.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Determine whether the account is currently locked due to failed login attempts.
     */
    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * Whole minutes remaining until the lock expires (minimum 1 while locked).
     */
    public function lockMinutesRemaining(): int
    {
        if (! $this->isLocked()) {
            return 0;
        }

        return max(1, (int) ceil(now()->diffInMinutes($this->locked_until)));
    }

    /**
     * Clear the login attempt counter and any active lock.
     */
    public function resetLoginAttempts(): void
    {
        $this->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'program_user')->withTimestamps();
    }

    public function getAllProgramsAttribute()
    {
        $programs = $this->programs()->get()->pluck('id');

        if ($this->program_id && !$programs->contains($this->program_id)) {
            $programs->push($this->program_id);
        }

        return $programs->unique()->values();
    }
}
