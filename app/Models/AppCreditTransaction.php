<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppCreditTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'amount' => 'decimal:2',
        'amount_per_order' => 'decimal:2',
        'orders_remaining_before' => 'integer',
        'orders_remaining_after' => 'integer',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Helpers
    public function getTransactionTypeText()
    {
        return $this->type_of_transaction == 1 ? 'إضافة' : 'سحب';
    }
}
