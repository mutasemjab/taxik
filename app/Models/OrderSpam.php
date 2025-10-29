<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderSpam extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
     protected $casts = [
        'trip_started_at' => 'datetime',
        'trip_completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'original_created_at' => 'datetime',
        'total_price_before_discount' => 'decimal:2',
        'total_price_after_discount' => 'decimal:2',
        'net_price_for_driver' => 'decimal:2',
        'commision_of_admin' => 'decimal:2',
        'pick_lat' => 'decimal:8',
        'pick_lng' => 'decimal:8',
        'drop_lat' => 'decimal:8',
        'drop_lng' => 'decimal:8',
    ];

    /**
     * Get the user that owns the spam order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service for the spam order
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the driver assigned to the spam order
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get status text
     */
    public function getStatusText()
    {
        return OrderStatus::from($this->status)->getStatusText();
    }

    /**
     * Get payment method text
     */
    public function getPaymentMethodText()
    {
        return PaymentMethod::from($this->payment_method)->getPaymentMethodText();
    }

    /**
     * Get payment status text
     */
    public function getPaymentStatusText()
    {
        return StatusPayment::from($this->status_payment)->getPaymentStatusText();
    }

    /**
     * Calculate distance between pick and drop points
     */
    public function getDistance()
    {
        if (!$this->drop_lat || !$this->drop_lng) {
            return null;
        }

        $earthRadius = 6371; // Earth's radius in kilometers
        
        $dLat = deg2rad($this->drop_lat - $this->pick_lat);
        $dLng = deg2rad($this->drop_lng - $this->pick_lng);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($this->pick_lat)) * cos(deg2rad($this->drop_lat)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        return round($distance, 2);
    }
    
}
