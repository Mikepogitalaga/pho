<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function coordinators(): BelongsToMany
    {
        return $this->belongsToMany(Coordinator::class)->withTimestamps();
    }

    public function getAssignedCoordinatorsAttribute()
    {
        return $this->coordinators->pluck('full_name')->implode(', ');
    }
}

