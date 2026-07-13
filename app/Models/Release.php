<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    use HasFactory;

    protected $fillable = [
        'release_number',
        'pas_number',
        'health_program_coordinator',
        'ptr_itr_ris_no',
        'pho_code',
        'source_docs_ptr_po_no',
        'facility_name',
        'received_by',
        'date_released',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_released' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(ReleaseItem::class);
    }
}
