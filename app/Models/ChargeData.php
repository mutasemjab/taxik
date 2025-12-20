<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeData extends Model
{
    use HasFactory;

    protected $guarded = [];

     protected $table = 'charge_data';

    public function countryCharge()
    {
        return $this->belongsTo(CountryCharge::class);
    }
}
