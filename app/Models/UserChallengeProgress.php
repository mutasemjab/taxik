<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserChallengeProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'current_count',
        'is_completed',
        'completed_at',
        'times_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'current_count' => 'integer',
        'times_completed' => 'integer',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage()
    {
        if ($this->challenge->target_count == 0) {
            return 0;
        }

        $percentage = ($this->current_count / $this->challenge->target_count) * 100;
        return min($percentage, 100);
    }

    /**
     * Check and complete challenge if target is reached
     */
    public function checkAndComplete()
    {
        if ($this->is_completed || $this->current_count < $this->challenge->target_count) {
            return false;
        }

        // Check if user has exceeded max completions
        if ($this->times_completed >= $this->challenge->max_completions_per_user) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Mark as completed
            $this->update([
                'is_completed' => true,
                'completed_at' => Carbon::now(),
                'times_completed' => $this->times_completed + 1,
            ]);

            // Add reward to user wallet
            $this->user->increment('balance', $this->challenge->reward_amount);

            // Create wallet transaction
            WalletTransaction::create([
                'user_id' => $this->user_id,
                'amount' => $this->challenge->reward_amount,
                'type_of_transaction' => 1, // Add
                'note' => 'Challenge reward: ' . $this->challenge->getTitle() . ' - ' . $this->challenge->getTitle('ar'),
            ]);

            // Reset for next round if allowed
            if ($this->times_completed < $this->challenge->max_completions_per_user) {
                $this->update([
                    'current_count' => 0,
                    'is_completed' => false,
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Increment progress
     */
    public function incrementProgress($amount = 1)
    {
        $this->increment('current_count', $amount);
        $this->refresh();
        
        return $this->checkAndComplete();
    }
}