<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Release;

class Pas extends Model
{
    use HasFactory, Auditable;

    protected $table = 'property_allocation_slips';

    protected $fillable = [
        'pas_number',
        'date_of_pass',
        'date_released',
        'supplier_id',
        'purpose_activity',
        'reason_for_transfer',
        'facility_name',
        'facility_coordinator',
        'transfer_type',
        'program',
        'status',
        'notes',
        'requested_by',
        'request_status',
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

    public function release()
    {
        return $this->hasOne(Release::class, 'pas_number', 'pas_number')->latestOfMany();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
