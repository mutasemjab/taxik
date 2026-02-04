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
        'activate' => 'integer',
    ];

    /**
     * Boot method - حساب المبلغ لكل رحلة تلقائياً
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($distribution) {
            if ($distribution->number_of_orders > 0) {
                $distribution->amount_per_order = $distribution->total_amount / $distribution->number_of_orders;
            }
        });

        static::updating(function ($distribution) {
            if ($distribution->number_of_orders > 0) {
                $distribution->amount_per_order = $distribution->total_amount / $distribution->number_of_orders;
            }
        });
    }

    /**
     * ✅ تطبيق التوزيع على رصيد التطبيق لجميع المستخدمين
     */
    public function applyToAllUsers()
    {
        $users = User::where('activate', 1)->get();

        foreach ($users as $user) {
            $user->applyAppCreditDistribution(
                $this->total_amount,
                $this->number_of_orders,
                auth('admin')->id() ?? null
            );
        }

        \Log::info("App credit distribution {$this->id} applied to " . $users->count() . " users");
    }

    /**
     * ✅ إزالة التوزيع من رصيد التطبيق لجميع المستخدمين
     */
    public static function removeFromAllUsers()
    {
        $users = User::where('activate', 1)->get();

        foreach ($users as $user) {
            $user->removeAppCreditDistribution();
        }

        \Log::info("App credit distribution removed from " . $users->count() . " users");
    }
}
