<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReleaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'release_id',
        'item_id',
        'item_description',
        'category',
        'quantity_released',
        'uom',
        'lot_number',
        'unit_cost',
    ];

    public function release()
    {
        return $this->belongsTo(Release::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
