<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverAlert extends Model
{
    use HasFactory;
    protected $guarded = [];

    
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}

