<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverRegistrationPayment extends Model
{
    use HasFactory;

   protected $guarded = [];
    
     protected $casts = [
        'total_paid' => 'double',
        'amount_kept' => 'double',
        'amount_added_to_wallet' => 'double',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
