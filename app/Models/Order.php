<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
        'status_payment' => StatusPayment::class,
        'trip_started_at' => 'datetime',
        'trip_completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'arrived_at' => 'datetime',
        'wallet_amount_used' => 'decimal:2',
        'cash_amount_due' => 'decimal:2',
        'is_hybrid_payment' => 'boolean',
    ];

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('order')
            ->setDescriptionForEvent(fn(string $eventName) => "Order has been {$eventName}");
    }

        /**
     * Boot method - auto-generate tracking token
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->tracking_token)) {
                $order->tracking_token = self::generateTrackingToken();
            }
        });
    }

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

    // New relationships
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
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
        return match ($this->status) {
            OrderStatus::Pending => 'warning',
            OrderStatus::DriverAccepted => 'info',
            OrderStatus::DriverGoToUser => 'primary',
            OrderStatus::UserWithDriver => 'success',
            OrderStatus::Arrived => 'info',
            OrderStatus::waitingPayment => 'warning',
            OrderStatus::Delivered => 'success',
            OrderStatus::UserCancelOrder => 'danger',
            OrderStatus::DriverCancelOrder => 'danger',
            OrderStatus::CancelCronJob => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusText()
    {
        return match ($this->status) {
            OrderStatus::Pending => __('messages.Pending'),
            OrderStatus::DriverAccepted => __('messages.Accepted'),
            OrderStatus::DriverGoToUser => __('messages.On_Way'),
            OrderStatus::UserWithDriver => __('messages.Started'),
            OrderStatus::Arrived => __('messages.Arrived'),
            OrderStatus::waitingPayment => __('messages.Waiting_Payment'),
            OrderStatus::Delivered => __('messages.Completed'),
            OrderStatus::UserCancelOrder => __('messages.User_Cancelled'),
            OrderStatus::DriverCancelOrder => __('messages.Driver_Cancelled'),
            OrderStatus::CancelCronJob => __('messages.Auto_Cancelled'),
            default => __('messages.Unknown'),
        };
    }

    public function isCancelled()
    {
        return in_array($this->status, [
            OrderStatus::UserCancelOrder,
            OrderStatus::DriverCancelOrder,
            OrderStatus::CancelCronJob,
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

    public function getTotalWaitingCharges()
    {
        return $this->waiting_charges + $this->in_trip_waiting_charges;
    }

    public static function generateTrackingToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32)); // 64 character hex string
        } while (self::where('tracking_token', $token)->exists());

        return $token;
    }

    /**
     * Get tracking URL
     */
    public function getTrackingUrl(): string
    {
        return url("/track-order/{$this->tracking_token}");
    }

    /**
     * Check if order can be tracked
     */
    public function canBeTracked(): bool
    {
        // Can be tracked if NOT in these statuses
        return !in_array($this->status, [
            OrderStatus::Pending,
            OrderStatus::Delivered,
            OrderStatus::UserCancelOrder,
            OrderStatus::DriverCancelOrder,
            OrderStatus::CancelCronJob,
        ]);
    }

    /**
     * Get tracking status text
     */
    public function getTrackingStatusText(): string
    {
        if (!$this->canBeTracked()) {
            return 'Tracking not available';
        }

        $statuses = [
            'accepted' => 'Driver Accepted - Preparing',
            'on_the_way' => 'Driver On The Way',
            'arrived' => 'Driver Arrived',
            'started' => 'Trip Started',
            'waiting_payment' => 'Waiting for Payment',
        ];

        return $statuses[$this->status->value] ?? 'In Progress';
    }


}
