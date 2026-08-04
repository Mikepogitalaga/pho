<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pas extends Model
{
    use HasFactory;

    protected $table = 'property_allocation_slips';

    protected $fillable = [
        'pas_number',
        'date_of_pass',
        'date_released',
        'supplier_id',
        'purpose_activity',
        'facility_name',
        'facility_coordinator',
        'transfer_type',
        'program',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_of_pass'   => 'date',
        'date_released'  => 'date',
    ];

    public function items()
    {
        return $this->hasMany(PasItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
