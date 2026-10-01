<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'task_id';
    public $timestamps = false;

    protected $fillable = ['project_id', 'title', 'brief', 'status'];

    // ---- relationships -------------------------------------------------
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class, 'task_id', 'task_id');
    }
}
