<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receiving extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_number',
        'po_number',
        'source_document_number',
        'ics_ptr_ris',
        'document_date',
        'supplier_id',
        'date_received',
        'received_by',
        'location',
        'stock_keeping_unit',
        'program_coordinator',
        'notes',
    ];

    protected $casts = [
        'date_received' => 'date',
        'document_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(ReceivingItem::class);
    }
}
