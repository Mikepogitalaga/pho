<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'category',
        'unit',
        'description',
        'quantity_on_hand',
        'reorder_level',
        'location',
        'unit_cost',
        'stock_keeping_unit',
        'program_coordinator',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    public function receivingItems()
    {
        return $this->hasMany(ReceivingItem::class);
    }

    public function nextExpiryItem()
    {
        return $this->hasOne(ReceivingItem::class)
            ->whereNotNull('expiry_date')
            ->orderBy('expiry_date');
    }

    public function releaseItems()
    {
        return $this->hasMany(ReleaseItem::class);
    }

    public function getExpiryDateAttribute()
    {
        $expiry = $this->nextExpiryItem?->expiry_date;

        return $expiry ? Carbon::parse($expiry) : null;
    }

    public function getExpiryStatusAttribute()
    {
        if (! $this->expiry_date) {
            return null;
        }

        if ($this->expiry_date->isPast()) {
            return 'Expired';
        }

        if ($this->expiry_date->lessThanOrEqualTo(now()->addDays(30))) {
            return 'Expiring Soon';
        }

        return 'Valid';
    }

    public function getExpiryLabelAttribute()
    {
        if (! $this->expiry_date) {
            return 'No expiry data';
        }

        if ($this->expiry_status === 'Expired') {
            return 'Expired ' . $this->expiry_date->diffForHumans(['parts' => 1, 'join' => true, 'short' => true]);
        }

        if ($this->expiry_status === 'Expiring Soon') {
            return 'Expires ' . $this->expiry_date->diffForHumans(['parts' => 1, 'join' => true, 'short' => true]);
        }

        return 'Expires on ' . $this->expiry_date->format('M d, Y');
    }

    public function getExpiryBadgeClassAttribute()
    {
        return match ($this->expiry_status) {
            'Expired' => 'badge-danger',
            'Expiring Soon' => 'badge-warning',
            'Valid' => 'badge-success',
            default => 'badge-secondary',
        };
    }

    public function getStatusAttribute()
    {
        if ($this->quantity_on_hand <= 0) {
            return 'Out of Stock';
        }

        $effectiveReorderLevel = ($this->reorder_level ?? 0) > 0 ? $this->reorder_level : 20;

        if ($this->quantity_on_hand <= $effectiveReorderLevel) {
            return 'Low Stock';
        }


        return 'Available';
    }


    public function getStatusClassAttribute()
    {
        return match ($this->status) {
            'Out of Stock' => 'badge-danger',
            'Low Stock' => 'badge-warning',
            default => 'badge-success',
        };
    }
}
