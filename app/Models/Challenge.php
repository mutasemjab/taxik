<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Challenge extends Model
{
    use HasFactory;

    const CHALLENGE_TYPES = [
        'referral' => [
            'en' => 'Invite Friends',
            'ar' => 'دعوة أصدقاء'
        ],
        'trips' => [
            'en' => 'Complete Trips',
            'ar' => 'إكمال رحلات'
        ],
        'spending' => [
            'en' => 'Total Spending',
            'ar' => 'إجمالي الإنفاق'
        ],
    ];

    protected $fillable = [
        'title_en',
        'title_ar',
        'description_en',
        'description_ar',
        'challenge_type',
        'target_count',
        'reward_amount',
        'start_date',
        'end_date',
        'is_active',
        'max_completions_per_user',
        'icon',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'target_count' => 'integer',
        'reward_amount' => 'float',
        'max_completions_per_user' => 'integer',
    ];

    /**
     * Relationships
     */
    public function userProgress()
    {
        return $this->hasMany(UserChallengeProgress::class);
    }

    /**
     * Get title based on language
     */
    public function getTitle($lang = 'en')
    {
        return $lang === 'ar' ? $this->title_ar : $this->title_en;
    }

    /**
     * Get description based on language
     */
    public function getDescription($lang = 'en')
    {
        return $lang === 'ar' ? $this->description_ar : $this->description_en;
    }

    /**
     * Get challenge type text
     */
    public function getChallengeTypeText($lang = 'en')
    {
        if (isset(self::CHALLENGE_TYPES[$this->challenge_type])) {
            return self::CHALLENGE_TYPES[$this->challenge_type][$lang] ?? self::CHALLENGE_TYPES[$this->challenge_type]['en'];
        }
        return $this->challenge_type;
    }

    /**
     * Check if challenge is currently active
     */
    public function isCurrentlyActive()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Scope for active challenges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', Carbon::now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::now());
            });
    }

    /**
     * Scope by challenge type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('challenge_type', $type);
    }
}