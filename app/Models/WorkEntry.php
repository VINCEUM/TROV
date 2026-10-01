<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkEntry extends Model
{
    protected $table = 'work_entries';
    protected $primaryKey = 'work_entry_id';
    public $timestamps = false;

    protected $fillable = ['assignment_id', 'worker_id', 'attendance_id', 'started_at', 'ended_at', 'description', 'eod_status', 'handover_note'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    // ---- relationships -------------------------------------------------
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TaskAssignment::class, 'assignment_id', 'assignment_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id', 'user_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_id', 'attendance_id');
    }

    public function activityCaptures(): HasMany
    {
        return $this->hasMany(ActivityCapture::class, 'work_entry_id', 'work_entry_id');
    }
}
