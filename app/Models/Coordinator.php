<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coordinator extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'position',
        'contact_number',
        'email',
    ];

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class)->withTimestamps();
    }

    public function getAssignedProgramsAttribute()
    {
        return $this->programs->pluck('name')->implode(', ');
    }
}

