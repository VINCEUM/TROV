<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * USERS - an Owner or a Video Editor. The role attribute keeps one identity
 * for every action a person takes (Chapter 3, Entity Relationship Diagram).
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public const ROLE_OWNER  = 'Owner';
    public const ROLE_EDITOR = 'Video Editor';

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = ['name', 'email']; // role, is_active, password_hash set explicitly
    protected $hidden = ['password_hash', 'remember_token'];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /** Laravel authenticates against this column instead of "password". */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    // ---- relationships -------------------------------------------------
    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class, 'worker_id', 'user_id');
    }

    public function devotionalSubmissions(): HasMany
    {
        return $this->hasMany(DevotionalSubmission::class, 'worker_id', 'user_id');
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'worker_id', 'user_id');
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class, 'worker_id', 'user_id');
    }

    public function workEntries(): HasMany
    {
        return $this->hasMany(WorkEntry::class, 'worker_id', 'user_id');
    }
}
