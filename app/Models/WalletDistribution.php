<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletDistribution extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'total_amount' => 'decimal:2',
        'number_of_orders' => 'integer',
        'amount_per_order' => 'decimal:2',
        'activate' => 'integer'
    ];

    /**
     * حساب المبلغ لكل رحلة تلقائياً
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($distribution) {
            $distribution->amount_per_order = round($distribution->total_amount / $distribution->number_of_orders, 2);
        });

        static::updating(function ($distribution) {
            $distribution->amount_per_order = round($distribution->total_amount / $distribution->number_of_orders, 2);
        });
    }

    /**
     * تطبيق هذا التوزيع على جميع المستخدمين
     */
    public function applyToAllUsers()
    {
        \DB::table('users')->update([
            'wallet_amount_per_order' => $this->amount_per_order,
            'wallet_orders_remaining' => $this->number_of_orders,
        ]);

        \Log::info("Applied wallet distribution {$this->id} to all users: {$this->amount_per_order} JD per order for {$this->number_of_orders} orders");
    }

    /**
     * إلغاء التوزيع من جميع المستخدمين
     */
    public static function removeFromAllUsers()
    {
        \DB::table('users')->update([
            'wallet_amount_per_order' => 0,
            'wallet_orders_remaining' => 0,
        ]);

        \Log::info("Removed wallet distribution from all users");
    }
}
