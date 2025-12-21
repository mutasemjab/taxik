<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable ,LogsActivity;

   protected $guarded = [];

   protected $hidden = [
      'password',
      'remember_token',
   ];

   // Append the photo_url attribute to JSON responses
    protected $appends = ['photo_url'];
    
     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']) // Log all attributes, or specify: ['name', 'price', 'number_of_cards']
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs()
            ->useLogName('user') // Custom log name
            ->setDescriptionForEvent(fn(string $eventName) => "User has been {$eventName}");
    }

       public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
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
    
    public function addBalance($amount, $note = null, $adminId = null, $userId = null)
    {
        $this->increment('balance', $amount);

        return $this->walletTransactions()->create([
            'user_id' => $userId,
            'admin_id' => $adminId,
            'amount' => $amount,
            'type_of_transaction' => WalletTransaction::TYPE_ADD,
            'note' => $note,
        ]);
    }
    
}
