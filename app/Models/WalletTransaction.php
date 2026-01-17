<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WalletTransaction extends Model
{
    use HasFactory,LogsActivity;

     protected $guarded = ['id'];

      protected $casts = [
        'amount' => 'decimal:2',
        'type_of_transaction' => 'integer',
    ];

    // Constants for type_of_transaction
    const TYPE_ADD = 1;
    const TYPE_WITHDRAWAL = 2;
     
     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']) // Log all attributes, or specify: ['name', 'price', 'number_of_cards']
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs()
            ->useLogName('wallet_transaction') // Custom log name
            ->setDescriptionForEvent(fn(string $eventName) => "Wallet Transaction has been {$eventName}");
    }

      public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the driver associated with the transaction.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the admin who created the transaction.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the transaction type text.
     *
     * @return string
     */
    public function getTransactionTypeText()
    {
        return $this->type_of_transaction == 1 
               ? __('messages.Deposit') 
               : __('messages.Withdrawal');
    }

    /**
     * Get the entity type (user or driver).
     *
     * @return string
     */
    public function getEntityType()
    {
        if ($this->user_id) {
            return 'user';
        } elseif ($this->driver_id) {
            return 'driver';
        } else {
            return 'unknown';
        }
    }

    /**
     * Get the entity ID (user_id or driver_id).
     *
     * @return int|null
     */
    public function getEntityId()
    {
        if ($this->user_id) {
            return $this->user_id;
        } elseif ($this->driver_id) {
            return $this->driver_id;
        } else {
            return null;
        }
    }

    /**
     * Get the entity name.
     *
     * @return string
     */
    public function getEntityName()
    {
        if ($this->user_id && $this->user) {
            return $this->user->name;
        } elseif ($this->driver_id && $this->driver) {
            return $this->driver->name;
        } else {
            return __('messages.Unknown');
        }
    }

    /**
     * Get formatted amount with sign.
     *
     * @return string
     */
    public function getFormattedAmount()
    {
        $prefix = $this->type_of_transaction == 1 ? '+' : '-';
        return $prefix . $this->amount;
    }

}
