<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskAssignment extends Model
{
    protected $table = 'task_assignments';
    protected $primaryKey = 'assignment_id';
    public $timestamps = false;

    protected $fillable = ['task_id', 'worker_id', 'assigned_by', 'assigned_at', 'ended_at'];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    // ---- relationships -------------------------------------------------
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id', 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by', 'user_id');
    }

    public function workEntries(): HasMany
    {
        return $this->hasMany(WorkEntry::class, 'assignment_id', 'assignment_id');
    }
}
