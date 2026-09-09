<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_id',
        'item_id',
        'item_code',
        'item_description',
        'category',
        'purchase_source',
        'quantity_received',
        'uom',
        'lot_number',
        'expiry_date',
        'unit_cost',
        'location',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function receiving()
    {
        return $this->belongsTo(Receiving::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
