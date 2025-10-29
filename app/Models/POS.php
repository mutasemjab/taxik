<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POS extends Model
{
    use HasFactory;
    protected $table = 'p_o_s';
    protected $guarded = [];

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function getTotalCardsAttribute()
    {
        return $this->cards()->count();
    }

    /**
     * Get the total number of card numbers for this POS
     */
    public function getTotalCardNumbersAttribute()
    {
        return $this->cards()->withCount('cardNumbers')->get()->sum('card_numbers_count');
    }
}
