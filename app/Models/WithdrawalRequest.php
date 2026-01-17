<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WithdrawalRequest extends Model
{
    use HasFactory,LogsActivity;

    
     protected $guarded = ['id'];
    
     
      public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']) // Log all attributes, or specify: ['name', 'price', 'number_of_cards']
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs()
            ->useLogName('withdrawal') // Custom log name
            ->setDescriptionForEvent(fn(string $eventName) => "Withdrawal has been {$eventName}");
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
