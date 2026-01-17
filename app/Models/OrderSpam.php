<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;
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
        return OrderStatus::from($this->status);
    }

    /**
     * Get payment method text
     */
    public function getPaymentMethodText()
    {
        return PaymentMethod::from($this->payment_method);
    }

    /**
     * Get payment status text
     */
    public function getPaymentStatusText()
    {
        return StatusPayment::from($this->status_payment);
    }

     public function getCancellationTypeText()
    {
        $types = [
            'user_cancel_order' => __('messages.Cancelled_by_User'),
            'driver_cancel_order' => __('messages.Cancelled_by_Driver'),
            'cancel_cron_job' => __('messages.Auto_Cancelled_No_Driver'),
        ];

        return $types[$this->status] ?? __('messages.Unknown');
    }

    /**
     * Calculate distance between pick and drop points
     */
    public function getDistance()
    {
        if (!$this->drop_lat || !$this->drop_lng) {
            return 0;
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

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage()
    {
        if ($this->total_price_before_discount > 0) {
            $discount = $this->total_price_before_discount - $this->total_price_after_discount;
            return round(($discount / $this->total_price_before_discount) * 100, 2);
        }
        
        return 0;
    }

    /**
     * Get drivers who were notified about this order
     */
    public function driversNotified()
    {
        return $this->hasMany(OrderDriverNotified::class, 'order_id');
    }

    /**
     * Get total notified drivers count
     */
    public function getTotalNotifiedDrivers()
    {
        return $this->driversNotified()->count();
    }

    /**
     * Check if order had a driver assigned before cancellation
     */
    public function hadDriverAssigned()
    {
        return !is_null($this->driver_id);
    }

    /**
     * Get time from creation to cancellation in minutes
     */
    public function getTimeToCancellationMinutes()
    {
        if ($this->cancelled_at && $this->created_at) {
            return $this->created_at->diffInMinutes($this->cancelled_at);
        }
        
        return null;
    }

    /**
     * Format time to cancellation
     */
    public function getFormattedTimeToCancellation()
    {
        $minutes = $this->getTimeToCancellationMinutes();
        
        if (!$minutes) {
            return null;
        }

        if ($minutes < 60) {
            return $minutes . ' ' . __('messages.Minutes');
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes > 0) {
            return $hours . ' ' . __('messages.Hours') . ' ' . $remainingMinutes . ' ' . __('messages.Minutes');
        }

        return $hours . ' ' . __('messages.Hours');
    }
    
}
