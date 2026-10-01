<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'client_id';
    public $timestamps = false;

    protected $fillable = ['client_name', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ---- relationships -------------------------------------------------
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id', 'client_id');
    }
}
