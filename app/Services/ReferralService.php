<?php

namespace App\Services;

use App\Models\User;
use App\Models\Driver;
use App\Models\ReferralReward;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class ReferralService
{
    /**
     * Get setting value
     */
    private function getSetting($key, $default = 0)
    {
        return DB::table('settings')->where('key', $key)->value('value') ?? $default;
    }

    /**
     * Process referral when a new user registers
     * User can be referred by another User OR by a Driver
     */
    public function processUserReferral(User $newUser, $referralCode = null)
    {
        if (!$referralCode) {
            return [
                'success' => false,
                'message' => 'No referral code provided'
            ];
        }

        DB::beginTransaction();
        
        try {
            // Check if referral code belongs to a user
            $referrer = User::where('referral_code', $referralCode)->first();
            
            if ($referrer) {
                // Link the new user to the referrer
                $newUser->user_id = $referrer->id;
                $newUser->save();
                
                // Create referral reward tracking
                ReferralReward::create([
                    'referrer_id' => $referrer->id,
                    'referrer_type' => 'user',
                    'referred_id' => $newUser->id,
                    'referred_type' => 'user',
                    'orders_completed' => 0,
                    'reward_paid' => false,
                ]);
                
                \Log::info("User {$newUser->id} registered with referral code from user {$referrer->id}");
                
                DB::commit();
                
                return [
                    'success' => true,
                    'message' => 'Referral processed successfully',
                    'referrer_type' => 'user',
                    'referrer_id' => $referrer->id,
                    'referrer_name' => $referrer->name
                ];
            }
            
            // Check if referral code belongs to a driver
            $referrer = Driver::where('referral_code', $referralCode)->first();
            
            if ($referrer) {
                // Link the new user to the driver referrer
                $newUser->driver_id = $referrer->id;
                $newUser->save();
                
                // Create referral reward tracking
                ReferralReward::create([
                    'referrer_id' => $referrer->id,
                    'referrer_type' => 'driver',
                    'referred_id' => $newUser->id,
                    'referred_type' => 'user',
                    'orders_completed' => 0,
                    'reward_paid' => false,
                ]);
                
                \Log::info("User {$newUser->id} registered with referral code from driver {$referrer->id}");
                
                DB::commit();
                
                return [
                    'success' => true,
                    'message' => 'Referral processed successfully',
                    'referrer_type' => 'driver',
                    'referrer_id' => $referrer->id,
                    'referrer_name' => $referrer->name
                ];
            }
            
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Invalid referral code'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error("Error processing user referral: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error processing referral: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process reward when referred user completes an order
     * Pay reward for EACH completed order after reaching minimum threshold
     */
    public function processOrderCompletion($userId)
    {
        try {
            // Find all referral records where this user was referred
            $referralRewards = ReferralReward::where('referred_id', $userId)
                ->where('referred_type', 'user')
                ->get();
            
            $ordersNeededForReward = $this->getSetting('number_of_order_to_get_reward', 1);
            $usersNeededForReward = $this->getSetting('number_of_referral_user_to_reward', 1);
            
            foreach ($referralRewards as $referralReward) {
                // Increment order count for this specific referred user
                $referralReward->increment('orders_completed');
                
                \Log::info("Referral reward {$referralReward->id}: Orders completed {$referralReward->orders_completed}");
                
                // Check if referrer can receive rewards
                $canReceiveRewards = $this->checkIfReferrerCanReceiveRewards(
                    $referralReward->referrer_id,
                    $referralReward->referrer_type,
                    $ordersNeededForReward,
                    $usersNeededForReward
                );
                
                if ($canReceiveRewards) {
                    // Pay reward for THIS completed order
                    $this->payReferralRewardForOrder($referralReward);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Error processing order completion for referral: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if referrer has met the minimum requirements to start receiving rewards
     */
    private function checkIfReferrerCanReceiveRewards($referrerId, $referrerType, $ordersNeededForReward, $usersNeededForReward)
    {
        // Get all referrals by this referrer
        $allReferralsByReferrer = ReferralReward::where('referrer_id', $referrerId)
            ->where('referrer_type', $referrerType)
            ->get();
        
        // Count how many referred users have completed the required orders
        $qualifiedReferralsCount = $allReferralsByReferrer->filter(function ($referral) use ($ordersNeededForReward) {
            return $referral->orders_completed >= $ordersNeededForReward;
        })->count();
        
        return $qualifiedReferralsCount >= $usersNeededForReward;
    }
    
    /**
     * Pay the referral reward for a completed order
     */
    private function payReferralRewardForOrder(ReferralReward $referralReward)
    {
        DB::beginTransaction();
        
        try {
            // Determine reward amount based on WHO is the referrer (user or driver)
            if ($referralReward->referrer_type === 'user') {
                $rewardAmount = $this->getSetting('user_referral_user_reward', 0);
            } else {
                $rewardAmount = $this->getSetting('driver_referral_user_reward', 0);
            }
            
            if ($rewardAmount <= 0) {
                \Log::info("Referral reward amount is 0, skipping payment");
                DB::rollBack();
                return false;
            }
            
            // Get the referrer
            if ($referralReward->referrer_type === 'user') {
                $referrer = User::find($referralReward->referrer_id);
                
                if ($referrer) {
                    $referrer->addBalance(
                        $rewardAmount,
                        "Referral reward for order #{$referralReward->orders_completed} by user ID: {$referralReward->referred_id}",
                        null,
                        $referrer->id
                    );
                }
            } else {
                $referrer = Driver::find($referralReward->referrer_id);
                
                if ($referrer) {
                    $referrer->addBalance(
                        $rewardAmount,
                        "Referral reward for order #{$referralReward->orders_completed} by user ID: {$referralReward->referred_id}",
                        null,
                        $referrer->id
                    );
                }
            }
            
            // ✅ التعديل: دائماً قم بزيادة المبلغ الإجمالي
            if (!$referralReward->reward_paid) {
                // أول مرة - احفظ التاريخ وعلّم أنه تم الدفع
                $referralReward->update([
                    'reward_paid' => true,
                    'reward_amount' => $rewardAmount,
                    'reward_paid_at' => now(),
                ]);
            } else {
                // ✅ في المرات التالية - استمر في إضافة المكافآت
                $referralReward->increment('reward_amount', $rewardAmount);
            }
            
            \Log::info("Referral reward paid: {$rewardAmount} to {$referralReward->referrer_type} ID: {$referralReward->referrer_id} for order #{$referralReward->orders_completed}");
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error paying referral reward: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate a unique referral code
     */
    public function generateReferralCode($prefix = '')
    {
        do {
            $code = $prefix . strtoupper(Str::random(8));
            
            $existsInUsers = User::where('referral_code', $code)->exists();
            $existsInDrivers = Driver::where('referral_code', $code)->exists();
            
        } while ($existsInUsers || $existsInDrivers);
        
        return $code;
    }
    
    /**
     * Get referral statistics for a user
     */
    public function getUserReferralStats(User $user)
    {
        $ordersNeededForReward = $this->getSetting('number_of_order_to_get_reward', 1);
        $usersNeededForReward = $this->getSetting('number_of_referral_user_to_reward', 1);
        
        // Total referred users
        $totalReferredUsers = ReferralReward::where('referrer_id', $user->id)
            ->where('referrer_type', 'user')
            ->where('referred_type', 'user')
            ->count();
        
        // Qualified referred users (completed required orders)
        $qualifiedReferrals = ReferralReward::where('referrer_id', $user->id)
            ->where('referrer_type', 'user')
            ->where('orders_completed', '>=', $ordersNeededForReward)
            ->count();
        
        $totalEarnings = ReferralReward::where('referrer_id', $user->id)
            ->where('referrer_type', 'user')
            ->where('reward_paid', true)
            ->sum('reward_amount');
        
        return [
            'total_referrals' => $totalReferredUsers,
            'qualified_referrals' => $qualifiedReferrals,
            'qualified_referrals_needed' => $usersNeededForReward,
            'orders_per_user_needed' => $ordersNeededForReward,
            'total_earnings' => (float) $totalEarnings,
            'can_receive_rewards' => $qualifiedReferrals >= $usersNeededForReward,
        ];
    }
    
    /**
     * Get referral statistics for a driver
     */
    public function getDriverReferralStats(Driver $driver)
    {
        $ordersNeededForReward = $this->getSetting('number_of_order_to_get_reward', 1);
        $usersNeededForReward = $this->getSetting('number_of_referral_user_to_reward', 1);
        
        // Total referred users
        $totalReferredUsers = ReferralReward::where('referrer_id', $driver->id)
            ->where('referrer_type', 'driver')
            ->where('referred_type', 'user')
            ->count();
        
        // Qualified referred users (completed required orders)
        $qualifiedReferrals = ReferralReward::where('referrer_id', $driver->id)
            ->where('referrer_type', 'driver')
            ->where('orders_completed', '>=', $ordersNeededForReward)
            ->count();
        
        $totalEarnings = ReferralReward::where('referrer_id', $driver->id)
            ->where('referrer_type', 'driver')
            ->where('reward_paid', true)
            ->sum('reward_amount');
        
        return [
            'total_referrals' => $totalReferredUsers,
            'qualified_referrals' => $qualifiedReferrals,
            'qualified_referrals_needed' => $usersNeededForReward,
            'orders_per_user_needed' => $ordersNeededForReward,
            'total_earnings' => (float) $totalEarnings,
            'can_receive_rewards' => $qualifiedReferrals >= $usersNeededForReward,
        ];
    }
    
    /**
     * Get detailed referral list with reward status
     */
    public function getUserReferralList(User $user, $perPage = 15)
    {
        return ReferralReward::where('referrer_id', $user->id)
            ->where('referrer_type', 'user')
            ->with(['referred'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    /**
     * Get detailed referral list for driver
     */
    public function getDriverReferralList(Driver $driver, $perPage = 15)
    {
        return ReferralReward::where('referrer_id', $driver->id)
            ->where('referrer_type', 'driver')
            ->with(['referred'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}