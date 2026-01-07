<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    protected $casts = [
        'price' => 'decimal:2',
        'pos_commission_percentage' => 'decimal:2',
        'driver_recharge_amount' => 'decimal:2',
        'number_of_cards' => 'integer',
    ];

    /**
     * Get the POS that owns the card
     */
    public function pos()
    {
        return $this->belongsTo(POS::class);
    }

    /**
     * Get the card numbers for the card
     */
    public function cardNumbers()
    {
        return $this->hasMany(CardNumber::class);
    }

    /**
     * Generate unique card numbers and create card_numbers records
     */
    public function generateCardNumbers()
    {
        // Delete existing card numbers for this card
        $this->cardNumbers()->delete();

        $generatedNumbers = [];
        $attempts = 0;
        $maxAttempts = $this->number_of_cards * 10; // Prevent infinite loop

        while (count($generatedNumbers) < $this->number_of_cards && $attempts < $maxAttempts) {
            $attempts++;
            
            // Generate a random 16-digit number (like credit card)
            $number = $this->generateUniqueNumber();
            
            // Check if this number already exists in database
            if (!CardNumber::where('number', $number)->exists() && !in_array($number, $generatedNumbers)) {
                $generatedNumbers[] = $number;
            }
        }

        // Create card_numbers records
        foreach ($generatedNumbers as $number) {
            $this->cardNumbers()->create([
                'number' => $number,
                'activate' => 1, // active
                'status' => 2,   // not used
            ]);
        }

        return $generatedNumbers;
    }

    /**
     * Generate a unique 16-digit number
     */
    private function generateUniqueNumber()
    {
        // Generate 4 groups of 4 digits
        $groups = [];
        for ($i = 0; $i < 4; $i++) {
            $groups[] = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        return implode('', $groups);
    }
}
