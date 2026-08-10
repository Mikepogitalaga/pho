<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_name',
        'contact_person',
        'address',
        'phone_number',
        'email',
        'supplier_type',
    ];

    public function receivings()
    {
        return $this->hasMany(Receiving::class);
    }

    /**
     * Scope a query to only include DOH suppliers.
     */
    public function scopeDoh($query)
    {
        return $query->where('supplier_type', 'DOH');
    }

    /**
     * Scope a query to only include GSO suppliers.
     */
    public function scopeGso($query)
    {
        return $query->where('supplier_type', 'GSO');
    }

    /**
     * Check if supplier is DOH type.
     */
    public function isDoh(): bool
    {
        return $this->supplier_type === 'DOH';
    }

    /**
     * Check if supplier is GSO type.
     */
    public function isGso(): bool
    {
        return $this->supplier_type === 'GSO';
    }
}
