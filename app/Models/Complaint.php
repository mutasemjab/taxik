<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    const STATUS_PENDING = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_DONE = 3;

    
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 1:
                return __('messages.Pending');
            case 2:
                return __('messages.In_Progress');
            case 3:
                return __('messages.Done');
            default:
                return __('messages.Not_Available');
        }
    }

    /**
     * Get the status badge class
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 1:
                return 'warning';
            case 2:
                return 'info';
            case 3:
                return 'success';
            default:
                return 'secondary';
        }
    }

    /**
     * Get the user that owns the complaint
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the driver associated with the complaint
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the order associated with the complaint
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
