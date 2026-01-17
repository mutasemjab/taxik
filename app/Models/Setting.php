<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use HasFactory,LogsActivity;

    protected $guarded=['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']) // Log all attributes, or specify: ['name', 'price', 'number_of_cards']
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs()
            ->useLogName('setting') // Custom log name
            ->setDescriptionForEvent(fn(string $eventName) => "Setting has been {$eventName}");
    }
}
