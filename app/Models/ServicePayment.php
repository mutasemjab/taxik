<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\PaymentMethod;

class ServicePayment extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'payment_method'];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
    ];

    protected $appends = ['payment_method_text'];

    public function getPaymentMethodTextAttribute(): string
    {
        return match ($this->payment_method) {
            PaymentMethod::Cash => __('messages.Cash'),
            PaymentMethod::Visa => __('messages.Visa'),
            PaymentMethod::Wallet => __('messages.Wallet'),
            default => __('messages.Unknown'),
        };
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
