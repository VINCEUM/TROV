<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityCapture extends Model
{
    protected $table = 'activity_captures';
    protected $primaryKey = 'capture_id';
    public $timestamps = false;

    protected $fillable = ['work_entry_id', 'attendance_id', 'file_path', 'captured_at', 'keystroke_count', 'mouse_event_count', 'activity_level'];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
        ];
    }

    // ---- relationships -------------------------------------------------
    public function workEntry(): BelongsTo
    {
        return $this->belongsTo(WorkEntry::class, 'work_entry_id', 'work_entry_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_id', 'attendance_id');
    }
}
