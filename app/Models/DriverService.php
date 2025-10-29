<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverService extends Model
{
    use HasFactory;
    
     protected $table = 'driver_services';
    
    protected $fillable = [
        'driver_id',
        'service_id',
        'status'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
