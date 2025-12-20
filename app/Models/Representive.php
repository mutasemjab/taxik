<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Representive extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'commission',
    ];

    protected $casts = [
        'commission' => 'double',
    ];
}
