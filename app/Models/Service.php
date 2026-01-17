<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Service extends Model
{
    use HasFactory,LogsActivity;

    protected $guarded = ['id'];
    protected $appends = ['name','photo_url'];

  
       // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']) // Log all attributes, or specify: ['name', 'price', 'number_of_cards']
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs()
            ->useLogName('service') // Custom log name
            ->setDescriptionForEvent(fn(string $eventName) => "Service has been {$eventName}");
    }
    
    // Add a custom accessor for the photo URL
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            // Use the APP_URL from the .env file
            $baseUrl = rtrim(config('app.url'), '/');
            return $baseUrl . '/assets/admin/uploads/' . $this->photo;
        }
        
        return null;
    }
    
    public function getNameAttribute()
    {
        $locale = App::getLocale();
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }
    public function getName()
    {
        $locale = App::getLocale();
        return $locale == 'ar' ? $this->name_ar : $this->name_en;
    }

    public function servicePayments()
    {
        return $this->hasMany(ServicePayment::class);
    }

     public function drivers()
    {
        return $this->belongsToMany(Driver::class, 'driver_services')
            ->withPivot('status')
            ->withTimestamps();
    }

    // Add a direct relationship to driver_services
    public function driverServices()
    {
        return $this->hasMany(DriverService::class);
    }
    
    
    
    /**
     * Get the type of commission text.
     * 
     * @return string
     */
    public function getCommisionTypeText()
    {
        return $this->type_of_commision == 1 
               ? __('messages.Fixed_Amount') 
               : __('messages.Percentage');
    }


}
