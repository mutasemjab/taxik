<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralReward extends Model
{
    protected $fillable = [
        'referrer_id',
        'referrer_type',
        'referred_id',
        'referred_type',
        'orders_completed',
        'reward_paid',
        'reward_amount',
        'reward_paid_at',
    ];

    protected $casts = [
        'reward_paid' => 'boolean',
        'reward_amount' => 'decimal:2',
        'reward_paid_at' => 'datetime',
    ];

     /**
     * Get the referrer (User or Driver)
     */
    public function referrer()
    {
        return $this->morphTo(__FUNCTION__, 'referrer_type', 'referrer_id');
    }

    /**
     * Get the referred (User or Driver)
     */
    public function referred()
    {
        return $this->morphTo(__FUNCTION__, 'referred_type', 'referred_id');
    }
}
