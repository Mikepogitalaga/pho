<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpDistributionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'op_distribution_id', 'item_id',
        'patient_name', 'patient_age', 'patient_gender',
        'item_description', 'quantity', 'uom', 'unit_cost', 'lot_number',
    ];

    public function distribution()
    {
        return $this->belongsTo(OpDistribution::class, 'op_distribution_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
