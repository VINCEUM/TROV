<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DevotionalSubmission extends Model
{
    protected $table = 'devotional_submissions';
    protected $primaryKey = 'devotional_id';
    public $timestamps = false;

    protected $fillable = ['worker_id', 'shift_assignment_id', 'workday', 'file_path', 'submitted_at'];

    protected function casts(): array
    {
        return [
            'workday' => 'date',
            'submitted_at' => 'datetime',
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

    public function attendanceSession(): HasOne
    {
        return $this->hasOne(AttendanceSession::class, 'devotional_id', 'devotional_id');
    }
}
