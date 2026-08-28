<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'name',
        'category',
        'address',
        'contact_person',
        'phone_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public static function categories(): array
    {
        return [
            'Hospitals',
            'RHU\'s',
            'NLAs/Other Agencies',
            'PHO Clinic',
        ];
    }
}
