<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $guarded=[];
    
      /**
     * Get the user that owns the rating.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the driver that owns the rating.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get rating badge color.
     */
    public function getRatingBadgeAttribute()
    {
        return [
            5 => 'success',
            4 => 'info',
            3 => 'warning',
            2 => 'orange',
            1 => 'danger',
        ][$this->rating] ?? 'secondary';
    }

    /**
     * Get stars display.
     */
    public function getStarsAttribute()
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $stars .= '<i class="fas fa-star text-warning"></i>';
            } else {
                $stars .= '<i class="far fa-star text-warning"></i>';
            }
        }
        return $stars;
    }
}
