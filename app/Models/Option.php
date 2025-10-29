<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function drivers()
    {
        return $this->belongsToMany(Driver::class, 'driver_options')
            ->withTimestamps();
    }
}
