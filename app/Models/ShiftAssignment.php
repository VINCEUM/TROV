<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ShiftAssignment extends Model
{
    protected $table = 'shift_assignments';
    protected $primaryKey = 'shift_assignment_id';
    public $timestamps = false;

    protected $fillable = ['worker_id', 'shift_type', 'scheduled_start', 'scheduled_end', 'assigned_by'];

    /**
     * The worker's shift for today, creating a default day shift if an owner
     * has not assigned one — so an editor is never blocked by a missing shift.
     */
    public static function ensureForToday(int $workerId): self
    {
        $today = Carbon::today();

        $shift = static::where('worker_id', $workerId)
            ->whereDate('scheduled_start', $today)
            ->first();

        if ($shift) {
            return $shift;
        }

        return static::create([
            'worker_id'       => $workerId,
            'shift_type'      => 'Morning',
            'scheduled_start' => $today->copy()->setTime(8, 0),
            'scheduled_end'   => $today->copy()->setTime(17, 0),
            'assigned_by'     => User::where('role', User::ROLE_OWNER)->value('user_id') ?? $workerId,
        ]);
    }

    protected function casts(): array
    {
        return [
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
        ];
    }

    // ---- relationships -------------------------------------------------
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id', 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by', 'user_id');
    }

    public function devotionalSubmissions(): HasMany
    {
        return $this->hasMany(DevotionalSubmission::class, 'shift_assignment_id', 'shift_assignment_id');
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'shift_assignment_id', 'shift_assignment_id');
    }
}
