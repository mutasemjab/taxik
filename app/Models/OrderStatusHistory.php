<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    use HasFactory;

     public $timestamps = false;
    
    protected $guarded = [];
    
    protected $casts = [
        'changed_at' => 'datetime'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
