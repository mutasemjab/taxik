<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use App\Models\ReferralReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserReferralController extends Controller
{
    protected $referralService;
    
    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }
    
    /**
     * Get comprehensive referral information
     */
    public function getReferralInfo(Request $request)
    {
        try {
            $user = Auth::guard('user-api')->user();
            
            // Get settings
            $ordersRequiredForReward = $this->getSetting('number_of_order_to_get_reward', 1);
            $usersRequiredForReward = $this->getSetting('number_of_referral_user_to_reward', 5);
            $userReferralReward = $this->getSetting('user_referral_user_reward', 5);
            
            // Get all referrals
            $allReferrals = ReferralReward::where('referrer_id', $user->id)
                ->where('referrer_type', 'user')
                ->with(['referred'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Calculate totals
            $totalReferrals = $allReferrals->count();
            
            // Count qualified referrals (users who completed required orders)
            $qualifiedReferrals = $allReferrals->filter(function ($referral) use ($ordersRequiredForReward) {
                return $referral->orders_completed >= $ordersRequiredForReward;
            })->count();
            
            $totalEarnings = $allReferrals->where('reward_paid', true)->sum('reward_amount');
            
            // Check if user can receive rewards
            $canReceiveRewards = $qualifiedReferrals >= $usersRequiredForReward;
            
            // Paginate for list
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);
            
            // Format the list with all required details
            $formattedList = $allReferrals->map(function ($referral) use ($ordersRequiredForReward, $userReferralReward, $canReceiveRewards) {
                $referred = $referral->referred;
                $isQualified = $referral->orders_completed >= $ordersRequiredForReward;
                
                return [
                    'id' => $referral->id,
                    'referred_name' => $referred->name ?? 'N/A',
                    'referred_phone' => $referred->phone ?? 'N/A',
                    'referred_photo_url' => $referred->photo_url ?? null,
                    'referred_type' => 'user',
                    
                    // Progress details
                    'orders_completed' => $referral->orders_completed,
                    'orders_required' => $ordersRequiredForReward,
                    'orders_remaining' => max(0, $ordersRequiredForReward - $referral->orders_completed),
                    'progress_percentage' => min(100, ($referral->orders_completed / $ordersRequiredForReward) * 100),
                    'is_qualified' => $isQualified,
                    
                    // Reward details
                    'reward_paid' => $referral->reward_paid,
                    'reward_amount' => $referral->reward_paid ? (float) $referral->reward_amount : ($canReceiveRewards && $isQualified ? $userReferralReward : 0),
                    'expected_reward' => (float) $userReferralReward,
                    
                    // Status
                    'status' => $referral->reward_paid ? 'rewarded' : 
                               (!$canReceiveRewards ? 'waiting_for_more_referrals' : 
                               ($isQualified ? 'pending_payment' : 'in_progress')),
                    
                    // Dates
                    'joined_date' => $referral->created_at->format('Y-m-d H:i:s'),
                    'formatted_date' => $referral->created_at->diffForHumans(),
                    'reward_paid_at' => $referral->reward_paid_at ? $referral->reward_paid_at->format('Y-m-d H:i:s') : null,
                ];
            });
            
            // Paginate manually
            $total = $formattedList->count();
            $items = $formattedList->forPage($page, $perPage)->values();
            
            return response()->json([
                'status' => true,
                'data' => [
                    'referral_code' => $user->referral_code,
                    'total_referrals' => $totalReferrals,
                    'qualified_referrals' => $qualifiedReferrals,
                    'users_required_for_reward' => $usersRequiredForReward,
                    'orders_required_per_user' => $ordersRequiredForReward,
                    'can_receive_rewards' => $canReceiveRewards,
                    'total_earnings' => (float) $totalEarnings,
                    'reward_per_referral' => (float) $userReferralReward,
                    'referred_list' => $items,
                    'pagination' => [
                        'total' => $total,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => $total > 0 ? ceil($total / $perPage) : 1,
                    ]
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching referral information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get setting value
     */
    private function getSetting($key, $default = 0)
    {
        return DB::table('settings')->where('key', $key)->value('value') ?? $default;
    }
}