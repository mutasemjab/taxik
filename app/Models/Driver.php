<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Add all photo URL attributes to the appends array
    protected $appends = [
        'photo_url',
        'photo_of_car_url',
        'driving_license_front_url',
        'driving_license_back_url',
        'car_license_front_url',
        'car_license_back_url',
        'no_criminal_record_url',
    ];
    
    /**
     * Helper method to generate image URLs
     *
     * @param string|null $imageName
     * @return string|null
     */
    protected function getImageUrl($imageName)
    {
        if ($imageName) {
            $baseUrl = rtrim(config('app.url'), '/');
            return $baseUrl . '/assets/admin/uploads/' . $imageName;
        }
        
        return null;
    }
    
    // Accessor for photo URL
    public function getPhotoUrlAttribute()
    {
        return $this->getImageUrl($this->photo);
    }
    
    // Accessor for photo_of_car URL
    public function getPhotoOfCarUrlAttribute()
    {
        return $this->getImageUrl($this->photo_of_car);
    }
    
    // Accessor for driving_license_front URL
    public function getDrivingLicenseFrontUrlAttribute()
    {
        return $this->getImageUrl($this->driving_license_front);
    }
    
    // Accessor for driving_license_back URL
    public function getDrivingLicenseBackUrlAttribute()
    {
        return $this->getImageUrl($this->driving_license_back);
    }
    
    // Accessor for car_license_front URL
    public function getCarLicenseFrontUrlAttribute()
    {
        return $this->getImageUrl($this->car_license_front);
    }
    
    // Accessor for car_license_back URL
    public function getCarLicenseBackUrlAttribute()
    {
        return $this->getImageUrl($this->car_license_back);
    }
    public function getNoCriminalRecordUrlAttribute()
    {
        return $this->getImageUrl($this->no_criminal_record);
    }
    
   public function options()
    {
        return $this->belongsToMany(Option::class, 'driver_options')
            ->withTimestamps();
    }

       public function services()
    {
        return $this->belongsToMany(Service::class, 'driver_services')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function activeServices()
    {
        return $this->belongsToMany(Service::class, 'driver_services')
                    ->withPivot('status')
                    ->wherePivot('status', 1);
    }


    // Add a direct relationship to driver_services
    public function driverServices()
    {
        return $this->hasMany(DriverService::class);
    }
   
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

     public function addBalance($amount, $note = null, $adminId = null, $userId = null)
    {
        $this->increment('balance', $amount);

        return $this->walletTransactions()->create([
            'driver_id' => $userId,
            'admin_id' => $adminId,
            'amount' => $amount,
            'type_of_transaction' => WalletTransaction::TYPE_ADD,
            'note' => $note,
        ]);
    }

    /**
     * Withdraw balance from driver wallet
     */
    public function withdrawBalance($amount, $note = null, $adminId = null, $userId = null)
    {
        if ($this->balance < $amount) {
            return false; // Insufficient balance
        }

        $this->decrement('balance', $amount);

        return $this->walletTransactions()->create([
            'driver_id' => $userId,
            'admin_id' => $adminId,
            'amount' => $amount,
            'type_of_transaction' => WalletTransaction::TYPE_WITHDRAWAL,
            'note' => $note,
        ]);
    }

}
