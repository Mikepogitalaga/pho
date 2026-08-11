<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpDistribution extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'reference_number', 'date_distributed', 'distributed_by', 'status', 'notes',
    ];

    protected $casts = [
        'date_distributed' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(OpDistributionItem::class);
    }
}
