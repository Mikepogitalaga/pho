<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'release_number', 'pas_number', 'health_program_coordinator',
        'ptr_itr_ris_no', 'pho_code', 'source_docs_ptr_po_no',
        'facility_name', 'transfer_type', 'reason_for_transfer',
        'received_by', 'received_by_designation', 'received_by_date',
        'approved_by', 'approved_by_designation', 'approved_by_date',
        'released_by', 'released_by_designation', 'released_by_date',
        'delivered_by', 'delivered_by_designation', 'delivered_by_date',
        'date_released', 'status', 'status_reason', 'notes',
    ];

    protected $casts = [
        'date_released'      => 'date',
        'approved_by_date'   => 'date',
        'released_by_date'   => 'date',
        'delivered_by_date'  => 'date',
        'received_by_date'   => 'date',
    ];

    public function items()
    {
        return $this->hasMany(ReleaseItem::class);
    }
}
