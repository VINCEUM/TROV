<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    protected $table = 'attendance_sessions';
    protected $primaryKey = 'attendance_id';
    public $timestamps = false;

    protected $fillable = ['worker_id', 'shift_assignment_id', 'devotional_id', 'clock_in_at', 'clock_out_at', 'active_seconds', 'idle_seconds'];

    protected function casts(): array
    {
        return [
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
        ];
    }

    // ---- relationships -------------------------------------------------
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id', 'user_id');
    }

    public function shiftAssignment(): BelongsTo
    {
        return $this->belongsTo(ShiftAssignment::class, 'shift_assignment_id', 'shift_assignment_id');
    }

    public function devotional(): BelongsTo
    {
        return $this->belongsTo(DevotionalSubmission::class, 'devotional_id', 'devotional_id');
    }

    public function workEntries(): HasMany
    {
        return $this->hasMany(WorkEntry::class, 'attendance_id', 'attendance_id');
    }

    public function activityCaptures(): HasMany
    {
        return $this->hasMany(ActivityCapture::class, 'attendance_id', 'attendance_id');
    }
}
