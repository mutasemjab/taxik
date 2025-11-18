<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
        'status_payment' => StatusPayment::class,
        'trip_started_at' => 'datetime',
        'trip_completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public static function generateOrderNumber()
    {
        do {
            $number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
        } while (self::where('number', $number)->exists());

        return $number;
    }


    public function getPaymentMethodText()
    {
        return match ($this->payment_method) {
            PaymentMethod::Cash => __('messages.Cash'),
            PaymentMethod::Visa => __('messages.Visa'),
            PaymentMethod::Wallet => __('messages.Wallet'),
            default => __('messages.Unknown'),
        };
    }

    public function getPaymentStatusText()
    {
        return match ($this->status_payment) {
            StatusPayment::Pending => __('messages.Pending'),
            StatusPayment::Paid => __('messages.Paid'),
            default => __('messages.Unknown'),
        };
    }

    public function getPaymentStatusClass()
    {
        return match ($this->status_payment) {
            StatusPayment::Pending => 'warning',
            StatusPayment::Paid => 'success',
            default => 'secondary',
        };
    }

        public function getStatusClass()
    {
        return match($this->status) {
            OrderStatus::Pending => 'warning',
            OrderStatus::DriverAccepted => 'info',
            OrderStatus::DriverGoToUser => 'primary',
            OrderStatus::UserWithDriver => 'success',
            OrderStatus::Arrived => 'info',
            OrderStatus::waitingPayment => 'warning',
            OrderStatus::Delivered => 'success',
            OrderStatus::UserCancelOrder => 'danger',
            OrderStatus::DriverCancelOrder => 'danger',
            OrderStatus::CancelCronJob => 'secondary', // NEW
            default => 'secondary',
        };
    }

    public function getStatusText()
    {
        return match($this->status) {
            OrderStatus::Pending => __('messages.Pending'),
            OrderStatus::DriverAccepted => __('messages.Accepted'),
            OrderStatus::DriverGoToUser => __('messages.On_Way'),
            OrderStatus::UserWithDriver => __('messages.Started'),
            OrderStatus::Arrived => __('messages.Arrived'),
            OrderStatus::waitingPayment => __('messages.Waiting_Payment'),
            OrderStatus::Delivered => __('messages.Completed'),
            OrderStatus::UserCancelOrder => __('messages.User_Cancelled'),
            OrderStatus::DriverCancelOrder => __('messages.Driver_Cancelled'),
            OrderStatus::CancelCronJob => __('messages.Auto_Cancelled'), // NEW
            default => __('messages.Unknown'),
        };
    }

    public function isCancelled()
    {
        return in_array($this->status, [
            OrderStatus::UserCancelOrder,
            OrderStatus::DriverCancelOrder,
            OrderStatus::CancelCronJob, // NEW
        ]);
    }

    public function isCompleted()
    {
        return $this->status === OrderStatus::Delivered;
    }

    public function isInProgress()
    {
        return in_array($this->status, [
            OrderStatus::DriverAccepted,
            OrderStatus::DriverGoToUser,
            OrderStatus::UserWithDriver
        ]);
    }

    public function getDistance()
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($this->pick_lat);
        $lonFrom = deg2rad($this->pick_lng);
        $latTo = deg2rad($this->drop_lat);
        $lonTo = deg2rad($this->drop_lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return round($angle * $earthRadius, 2);
    }

    public function getFormattedDiscount()
    {
        return $this->discount_value ?: '0';
    }

    public function getDiscountPercentage()
    {
        if (!$this->discount_value || $this->total_price_before_discount == 0) {
            return 0;
        }

        return round(($this->discount_value / $this->total_price_before_discount) * 100, 1);
    }
}