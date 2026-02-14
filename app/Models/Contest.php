<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contest extends Model
{
    protected $fillable = [
        'title',
        'description',
        'deadline_at',
        'is_active'
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}