<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

   protected $guarded = [];
      /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the service associated with the coupon.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the discount type text.
     *
     * @return string
     */
    public function getDiscountTypeText()
    {
        return $this->discount_type == 1 
               ? __('messages.Fixed_Amount') 
               : __('messages.Percentage');
    }

    /**
     * Get the coupon type text.
     *
     * @return string
     */
    public function getCouponTypeText()
    {
        switch ($this->coupon_type) {
            case 1:
                return __('messages.All_Rides');
            case 2:
                return __('messages.First_Ride');
            case 3:
                return __('messages.Specific_Service');
            default:
                return __('messages.Unknown');
        }
    }

    /**
     * Get the formatted discount amount with appropriate symbol.
     *
     * @return string
     */
    public function getFormattedDiscount()
    {
        if ($this->discount_type == 1) {
            // Fixed amount
            return $this->discount;
        } else {
            // Percentage
            return $this->discount . '%';
        }
    }

    /**
     * Check if the coupon is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        $now = Carbon::now();
        return $this->activate == 1 && 
               $now->between($this->start_date, $this->end_date);
    }

    /**
     * Get the status of the coupon.
     *
     * @return string
     */
    public function getStatus()
    {
        if ($this->activate == 2) {
            return __('messages.Inactive');
        }

        $now = Carbon::now();
        
        if ($now->lt($this->start_date)) {
            return __('messages.Upcoming');
        } elseif ($now->gt($this->end_date)) {
            return __('messages.Expired');
        } else {
            return __('messages.Active');
        }
    }

    /**
     * Get the status class for display purposes.
     *
     * @return string
     */
    public function getStatusClass()
    {
        if ($this->activate == 2) {
            return 'danger';
        }

        $now = Carbon::now();
        
        if ($now->lt($this->start_date)) {
            return 'info';
        } elseif ($now->gt($this->end_date)) {
            return 'warning';
        } else {
            return 'success';
        }
    }
}
