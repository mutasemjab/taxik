<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Challenge;
use App\Models\UserChallengeProgress;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use Responses;

    /**
     * Get home page data including banners, user ban status, and challenges
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('user-api')->user();

            if (!$user) {
                return $this->error_response('Unauthenticated', null, 401);
            }

            // Get language from header (default to 'en')
            $lang = $request->header('lang', 'en');

            // Get banners
            $banners = Banner::orderBy('created_at', 'asc')->get();

            // Check user ban status
            $banStatus = $this->checkUserBanStatus($user, $lang);

            // Get active challenges with user progress
            $challenges = $this->getUserChallenges($user, $lang);

            // Get user statistics
            $userStats = $this->getUserStatistics($user);

            $responseData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'photo' => $user->photo ? asset('assets/admin/uploads/' . $user->photo) : null,
                    'balance' => $user->balance,
                    'referral_code' => $user->referral_code,
                    'activate' => $user->activate,
                ],
                'ban_status' => $banStatus,
                'banners' => $banners,
                'challenges' => $challenges,
                'statistics' => $userStats,
            ];

            return $this->success_response('Home data retrieved successfully', $responseData);
        } catch (\Exception $e) {
            \Log::error('Error retrieving home data: ' . $e->getMessage());
            return $this->error_response('Failed to retrieve home data', $e->getMessage());
        }
    }

    /**
     * Get user challenges with progress
     */
    public function getChallenges(Request $request)
    {
        try {
            $user = Auth::guard('user-api')->user();

            if (!$user) {
                return $this->error_response('Unauthenticated', null, 401);
            }

            // Get language from header (default to 'en')
            $lang = $request->header('lang', 'en');

            $challenges = $this->getUserChallenges($user, $lang);

            return $this->success_response('Challenges retrieved successfully', $challenges);
        } catch (\Exception $e) {
            \Log::error('Error retrieving challenges: ' . $e->getMessage());
            return $this->error_response('Failed to retrieve challenges', $e->getMessage());
        }
    }

    /**
     * Get specific challenge details with user progress
     */
    public function getChallengeDetails(Request $request, $id)
    {
        try {
            $user = Auth::guard('user-api')->user();

            if (!$user) {
                return $this->error_response('Unauthenticated', null, 401);
            }

            // Get language from header (default to 'en')
            $lang = $request->header('lang', 'en');

            $challenge = Challenge::active()->find($id);

            if (!$challenge) {
                return $this->error_response('Challenge not found or inactive', null, 404);
            }

            // Get or create user progress for this challenge
            $progress = $user->getChallengeProgress($challenge->id);

            $challengeData = [
                'id' => $challenge->id,
                'title' => $lang === 'ar' ? $challenge->title_ar : $challenge->title_en,
                'description' => $lang === 'ar' ? $challenge->description_ar : $challenge->description_en,
                'type' => $challenge->challenge_type,
                'type_text' => $challenge->getChallengeTypeText($lang),
                'target_count' => $challenge->target_count,
                'reward_amount' => $challenge->reward_amount,
                'icon' => $challenge->icon ? asset('assets/admin/uploads/challenges/' . $challenge->icon) : null,
                'start_date' => $challenge->start_date ? $challenge->start_date->format('Y-m-d') : null,
                'end_date' => $challenge->end_date ? $challenge->end_date->format('Y-m-d') : null,
                'max_completions' => $challenge->max_completions_per_user,
                'user_progress' => [
                    'current_count' => $progress->current_count,
                    'target_count' => $challenge->target_count,
                    'progress_percentage' => $progress->getProgressPercentage(),
                    'is_completed' => $progress->is_completed,
                    'times_completed' => $progress->times_completed,
                    'completed_at' => $progress->completed_at ? $progress->completed_at->format('Y-m-d H:i:s') : null,
                    'remaining_count' => max(0, $challenge->target_count - $progress->current_count),
                    'can_complete_again' => $progress->times_completed < $challenge->max_completions_per_user,
                ],
            ];

            return $this->success_response('Challenge details retrieved successfully', $challengeData);
        } catch (\Exception $e) {
            \Log::error('Error retrieving challenge details: ' . $e->getMessage());
            return $this->error_response('Failed to retrieve challenge details', $e->getMessage());
        }
    }

    /**
     * Check user ban status
     */
    private function checkUserBanStatus($user, $lang = 'en')
    {
        $activeBan = $user->activeBan;

        if (!$activeBan) {
            return [
                'is_banned' => false,
                'message' => null,
            ];
        }

        // Check if ban has expired
        if (!$activeBan->is_permanent && $activeBan->isExpired()) {
            // Auto-unban
            $user->unbanUser(null, 'Automatic unban - ban period expired');
            
            return [
                'is_banned' => false,
                'message' => null,
            ];
        }

        // User is currently banned
        $banInfo = [
            'is_banned' => true,
            'ban_type' => $activeBan->is_permanent ? 'permanent' : 'temporary',
            'reason' => $activeBan->getReasonText($lang),
            'description' => $activeBan->ban_description,
            'banned_at' => $activeBan->banned_at->format('Y-m-d H:i:s'),
            'message' => $activeBan->is_permanent 
                ? ($lang === 'ar' ? 'تم حظر حسابك بشكل دائم. يرجى التواصل مع الدعم.' : 'Your account has been permanently banned. Please contact support.')
                : ($lang === 'ar' ? 'تم حظر حسابك مؤقتاً حتى ' . $activeBan->ban_until->format('Y-m-d H:i') : 'Your account is temporarily banned until ' . $activeBan->ban_until->format('Y-m-d H:i')),
        ];

        if (!$activeBan->is_permanent) {
            $banInfo['ban_until'] = $activeBan->ban_until->format('Y-m-d H:i:s');
            $banInfo['remaining_time'] = $activeBan->getRemainingTime($lang);
        }

        return $banInfo;
    }

    /**
     * Get user challenges with progress
     */
    private function getUserChallenges($user, $lang = 'en')
    {
        $activeChallenges = Challenge::active()->get();

        return $activeChallenges->map(function ($challenge) use ($user, $lang) {
            // Get or create user progress for this challenge
            $progress = $user->getChallengeProgress($challenge->id);

            return [
                'id' => $challenge->id,
                'title' => $lang === 'ar' ? $challenge->title_ar : $challenge->title_en,
                'description' => $lang === 'ar' ? $challenge->description_ar : $challenge->description_en,
                'type' => $challenge->challenge_type,
                'type_text' => $challenge->getChallengeTypeText($lang),
                'target_count' => $challenge->target_count,
                'reward_amount' => $challenge->reward_amount,
                'icon' => $challenge->icon ? asset('assets/admin/uploads/challenges/' . $challenge->icon) : null,
                'start_date' => $challenge->start_date ? $challenge->start_date->format('Y-m-d') : null,
                'end_date' => $challenge->end_date ? $challenge->end_date->format('Y-m-d') : null,
                'progress' => [
                    'current_count' => $progress->current_count,
                    'target_count' => $challenge->target_count,
                    'progress_percentage' => round($progress->getProgressPercentage(), 1),
                    'is_completed' => $progress->is_completed,
                    'times_completed' => $progress->times_completed,
                    'completed_at' => $progress->completed_at ? $progress->completed_at->format('Y-m-d H:i:s') : null,
                    'remaining_count' => max(0, $challenge->target_count - $progress->current_count),
                    'can_complete_again' => $progress->times_completed < $challenge->max_completions_per_user,
                ],
            ];
        })->values();
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics($user)
    {
        return [
            'total_orders' => \App\Models\Order::where('user_id', $user->id)
                ->whereIn('status', [5, 6]) // Delivered or Completed
                ->count(),
            'total_spent' => \App\Models\Order::where('user_id', $user->id)
                ->whereIn('status', [5, 6])
                ->sum('total_price_after_discount'),
            'challenges_completed' => UserChallengeProgress::where('user_id', $user->id)
                ->where('times_completed', '>', 0)
                ->sum('times_completed'),
            'total_rewards_earned' => UserChallengeProgress::where('user_id', $user->id)
                ->where('times_completed', '>', 0)
                ->get()
                ->sum(function ($progress) {
                    return $progress->challenge->reward_amount * $progress->times_completed;
                }),
            'referral_count' => \App\Models\User::where('user_id', $user->id)->count(),
        ];
    }

    /**
     * Get user ban history
     */
    public function getBanHistory(Request $request)
    {
        try {
            $user = Auth::guard('user-api')->user();

            if (!$user) {
                return $this->error_response('Unauthenticated', null, 401);
            }

            // Get language from header (default to 'en')
            $lang = $request->header('lang', 'en');

            $bans = $user->bans()
                ->with(['admin', 'unbannedByAdmin'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($ban) use ($lang) {
                    return [
                        'id' => $ban->id,
                        'reason' => $ban->getReasonText($lang),
                        'description' => $ban->ban_description,
                        'type' => $ban->is_permanent ? 'permanent' : 'temporary',
                        'banned_at' => $ban->banned_at->format('Y-m-d H:i:s'),
                        'ban_until' => $ban->ban_until ? $ban->ban_until->format('Y-m-d H:i:s') : null,
                        'is_active' => $ban->is_active,
                        'status' => $ban->getStatusText($lang),
                        'unbanned_at' => $ban->unbanned_at ? $ban->unbanned_at->format('Y-m-d H:i:s') : null,
                        'unban_reason' => $ban->unban_reason,
                    ];
                });

            return $this->success_response('Ban history retrieved successfully', $bans);
        } catch (\Exception $e) {
            \Log::error('Error retrieving ban history: ' . $e->getMessage());
            return $this->error_response('Failed to retrieve ban history', $e->getMessage());
        }
    }

    /**
     * Get user challenge history (completed challenges)
     */
    public function getChallengeHistory(Request $request)
    {
        try {
            $user = Auth::guard('user-api')->user();

            if (!$user) {
                return $this->error_response('Unauthenticated', null, 401);
            }

            // Get language from header (default to 'en')
            $lang = $request->header('lang', 'en');

            $completedChallenges = UserChallengeProgress::where('user_id', $user->id)
                ->where('times_completed', '>', 0)
                ->with('challenge')
                ->orderBy('completed_at', 'desc')
                ->get()
                ->map(function ($progress) use ($lang) {
                    $challenge = $progress->challenge;
                    return [
                        'challenge_id' => $challenge->id,
                        'title' => $lang === 'ar' ? $challenge->title_ar : $challenge->title_en,
                        'type' => $challenge->challenge_type,
                        'type_text' => $challenge->getChallengeTypeText($lang),
                        'reward_amount' => $challenge->reward_amount,
                        'times_completed' => $progress->times_completed,
                        'total_rewards_earned' => $challenge->reward_amount * $progress->times_completed,
                        'last_completed_at' => $progress->completed_at ? $progress->completed_at->format('Y-m-d H:i:s') : null,
                        'icon' => $challenge->icon ? asset('assets/admin/uploads/challenges/' . $challenge->icon) : null,
                    ];
                });

            return $this->success_response('Challenge history retrieved successfully', $completedChallenges);
        } catch (\Exception $e) {
            \Log::error('Error retrieving challenge history: ' . $e->getMessage());
            return $this->error_response('Failed to retrieve challenge history', $e->getMessage());
        }
    }
}