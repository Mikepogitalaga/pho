<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasItem extends Model
{
    use HasFactory;

    protected $table = 'pas_items';

    protected $fillable = [
        'pas_id',
        'item_id',
        'item_description',
        'product_code',
        'lot_number',
        'expiration_date',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'unit_cost'       => 'decimal:2',
        'total_cost'      => 'decimal:2',
    ];

    public function pas()
    {
        return $this->belongsTo(Pas::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
