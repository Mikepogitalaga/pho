<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_person',
        'address',
        'phone_number',
        'email',
    ];

    public function receivings()
    {
        return $this->hasMany(Receiving::class);
    }
}
