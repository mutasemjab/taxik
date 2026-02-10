<?php

namespace App\Services;

use App\Models\User;
use App\Models\Driver;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralService
{
    /**
     * Process referral when a new user registers
     * 
     * @param User $newUser The newly registered user
     * @param string|null $referralCode The referral code used during registration
     * @return array Result of the referral process
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
                
                // Update referral challenge progress (rewards handled by Challenge system)
                $referrer->updateChallengeProgress('referral', 1);
                
                \Log::info("User {$newUser->id} registered with referral code from user {$referrer->id}. Challenge progress updated.");
                
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
     * Process referral when a new driver registers
     * 
     * Note: Since drivers table doesn't have user_id/driver_id columns,
     * we can only track if a USER referred this driver.
     * Driver-to-driver referrals are not currently supported in the database structure.
     * 
     * @param Driver $newDriver The newly registered driver
     * @param string|null $referralCode The referral code used during registration
     * @return array Result of the referral process
     */
    public function processDriverReferral(Driver $newDriver, $referralCode = null)
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
                // Since drivers table doesn't have user_id column,
                // we cannot directly link the driver to the user who referred them.
                // You would need to add a user_id column to drivers table, or
                // create a separate driver_referrals table to track this.
                
                // Update referral challenge progress for the user
                $referrer->updateChallengeProgress('referral', 1);
                
                \Log::info("Driver {$newDriver->id} registered with referral code from user {$referrer->id}. Challenge progress updated. Note: Driver table does not store referrer relationship.");
                
                DB::commit();
                
                return [
                    'success' => true,
                    'message' => 'Referral processed successfully',
                    'referrer_type' => 'user',
                    'referrer_id' => $referrer->id,
                    'referrer_name' => $referrer->name,
                    'note' => 'Driver referral counted for challenge, but not stored in driver record (add user_id column to drivers table to store this)'
                ];
            }
            
            // Check if referral code belongs to a driver
            $referrer = Driver::where('referral_code', $referralCode)->first();
            
            if ($referrer) {
                // Driver-to-driver referrals cannot be tracked in current structure
                // because drivers table doesn't have driver_id column
                
                \Log::info("Driver {$newDriver->id} attempted registration with driver referral code {$referralCode}, but driver-to-driver referrals not supported in current DB structure");
                
                DB::commit();
                
                return [
                    'success' => false,
                    'message' => 'Driver-to-driver referrals not currently supported',
                    'referrer_type' => 'driver',
                    'referrer_id' => $referrer->id,
                    'note' => 'Add driver_id column to drivers table to support driver-to-driver referrals'
                ];
            }
            
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Invalid referral code'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error("Error processing driver referral: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error processing referral: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate a unique referral code
     * 
     * @param string $prefix Prefix for the referral code (e.g., 'USER', 'DRIVER')
     * @return string Unique referral code
     */
    public function generateReferralCode($prefix = '')
    {
        do {
            $code = $prefix . strtoupper(Str::random(8));
            
            // Check if code exists in users or drivers table
            $existsInUsers = User::where('referral_code', $code)->exists();
            $existsInDrivers = Driver::where('referral_code', $code)->exists();
            
        } while ($existsInUsers || $existsInDrivers);
        
        return $code;
    }
    
    /**
     * Get referral statistics for a user
     * 
     * @param User $user
     * @return array Statistics about referrals
     */
    public function getUserReferralStats(User $user)
    {
        // Users referred by this user (stored in users.user_id)
        $referredUsersCount = User::where('user_id', $user->id)->count();
        
        // Drivers referred by this user (stored in users.driver_id points to the user who referred them)
        // Actually, when a user refers a driver, the driver row doesn't have user_id
        // The driver registration would need to be tracked differently
        // For now, we can only track users referring other users
        $referredDriversCount = 0; // Drivers table doesn't have user_id/driver_id columns
        
        $totalEarnings = DB::table('user_challenge_progress')
            ->join('challenges', 'user_challenge_progress.challenge_id', '=', 'challenges.id')
            ->where('user_challenge_progress.user_id', $user->id)
            ->where('challenges.challenge_type', 'referral')
            ->where('user_challenge_progress.is_completed', true)
            ->sum(DB::raw('challenges.reward_amount * user_challenge_progress.times_completed'));
        
        return [
            'total_referrals' => $referredUsersCount + $referredDriversCount,
            'referred_users' => $referredUsersCount,
            'referred_drivers' => $referredDriversCount,
            'total_earnings' => (float) $totalEarnings,
        ];
    }
    
    /**
     * Get referral statistics for a driver
     * 
     * @param Driver $driver
     * @return array Statistics about referrals
     */
    public function getDriverReferralStats(Driver $driver)
    {
        // Users referred by this driver (stored in users.driver_id)
        $referredUsersCount = User::where('driver_id', $driver->id)->count();
        
        // Drivers table doesn't have user_id/driver_id columns
        $referredDriversCount = 0;
        
        return [
            'total_referrals' => $referredUsersCount,
            'referred_users' => $referredUsersCount,
            'referred_drivers' => 0,
            'total_earnings' => 0, // Drivers don't have challenge system
        ];
    }
}