<?php


namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class HomeDriverController extends Controller
{
    use Responses;
    
    public function __invoke(Request $request)
    {
        // Get the authenticated driver
        $driver = auth('driver-api')->user();
        
        // Get language from header (default to 'en')
        $lang = $request->header('lang', 'en');
        
        // Get driver's rating
        $rating = $driver->ratings()->avg('rating') ?? 5;
        
        // Get only active services
        $activeServices = $driver->activeServices()->get();
        
        // Get driver's options
        $driverOptions = $driver->options()->get();
        
        // Get minimum wallet balance setting
        $minWalletBalance = DB::table('settings')
            ->where('key', 'minimum_money_in_wallet_driver_to_get_order')
            ->value('value') ?? 0;
        
        // Check if driver's balance is below minimum required
        $walletStatus = $this->checkWalletStatus($driver->balance, $minWalletBalance, $lang);
        
        // Get today's statistics
        $todayStats = $this->getTodayStatistics($driver->id);
        
        // Prepare the response data
        $responseData = [
            'profile' => $driver,
            'rating' => round($rating, 1),
            'active_services' => $activeServices,
            'options' => $driverOptions,
            'wallet_info' => [
                'current_balance' => $driver->balance,
                'minimum_required_balance' => $minWalletBalance,
                'is_eligible_for_orders' => $walletStatus['is_eligible'],
                'needs_recharge' => $walletStatus['needs_recharge'],
                'required_amount' => $walletStatus['required_amount'],
                'popup_message' => $walletStatus['popup_message']
            ],
            'today_statistics' => $todayStats
        ];
        
        // Add ban information if driver is banned
        if ($driver->activate == 2) {
            $responseData['ban_info'] = $this->getBanInfo($driver, $lang);
        }
        
        return $this->success_response('Home data retrieved successfully', $responseData);
    }
    
    /**
     * Get ban information for the driver
     */
    private function getBanInfo($driver, $lang)
    {
        $activeBan = $driver->activeBan;
        
        if (!$activeBan) {
            $message = $lang === 'ar' ? 'تم حظر حسابك.' : 'Your account has been banned.';
            
            return [
                'is_banned' => true,
                'message' => $message,
            ];
        }

        $message = $lang === 'ar' 
            ? 'تم حظر حسابك. يمكنك فقط عرض المعلومات وسحب رصيدك.' 
            : 'Your account has been banned. You can only view information and withdraw your balance.';

        $banInfo = [
            'is_banned' => true,
            'is_permanent' => $activeBan->is_permanent,
            'reason' => $activeBan->ban_reason,
            'reason_text' => $activeBan->getReasonText($lang),
            'description' => $activeBan->ban_description,
            'banned_at' => $activeBan->banned_at->toDateTimeString(),
            'banned_by' => $activeBan->admin ? $activeBan->admin->name : ($lang === 'ar' ? 'النظام' : 'System'),
            'message' => $message,
        ];

        if (!$activeBan->is_permanent && $activeBan->ban_until) {
            $banInfo['ban_until'] = $activeBan->ban_until->toDateTimeString();
            $banInfo['remaining_time'] = $activeBan->getRemainingTime($lang);
            $banInfo['remaining_time_human'] = $activeBan->ban_until->diffForHumans();
        } else {
            $banInfo['ban_until'] = null;
            $banInfo['remaining_time'] = $lang === 'ar' ? 'دائم' : 'Permanent';
            $banInfo['remaining_time_human'] = $lang === 'ar' ? 'حظر دائم' : 'Permanent ban';
        }

        return $banInfo;
    }
    
    /**
     * Check wallet status and determine if driver needs to recharge
     */
    private function checkWalletStatus($currentBalance, $minBalance, $lang)
    {
        $isEligible = $currentBalance >= $minBalance;
        $needsRecharge = !$isEligible;
        $requiredAmount = $needsRecharge ? ($minBalance - $currentBalance) : 0;
        
        $popupMessage = null;
        if ($needsRecharge) {
            $title = $lang === 'ar' ? 'يتطلب شحن المحفظة' : 'Wallet Recharge Required';
            $message = $lang === 'ar' 
                ? "رصيد محفظتك هو {$currentBalance}. تحتاج إلى {$minBalance} على الأقل لاستلام الطلبات. يرجى شحن محفظتك بمبلغ {$requiredAmount} أو أكثر لبدء استلام الطلبات."
                : "Your wallet balance is {$currentBalance}. You need at least {$minBalance} to receive orders. Please recharge your wallet with {$requiredAmount} or more to start receiving orders.";
            $actionText = $lang === 'ar' ? 'شحن المحفظة' : 'Recharge Wallet';
            
            $popupMessage = [
                'title' => $title,
                'message' => $message,
                'action_text' => $actionText,
                'show_popup' => true
            ];
        }
        
        return [
            'is_eligible' => $isEligible,
            'needs_recharge' => $needsRecharge,
            'required_amount' => $requiredAmount,
            'popup_message' => $popupMessage
        ];
    }
    
    /**
     * Get today's statistics for the driver
     */
    private function getTodayStatistics($driverId)
    {
        $today = now()->format('Y-m-d');
    
        // Get today's completed orders count
        $todayOrdersCount = DB::table('orders')
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->count();
    
        // Get today's earnings from completed orders
        $todayEarnings = DB::table('orders')
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->sum('net_price_for_driver');
    
        // Calculate today's distance
        $todayDistance = $this->calculateTodayDistance($driverId, $today);
    
        return [
            'orders_completed_today' => $todayOrdersCount,
            'earnings_today' => round($todayEarnings, 2),
            'distance_traveled_today' => [
                'meters' => round($todayDistance, 2),
                'kilometers' => round($todayDistance / 1000, 2)
            ],
            'date' => $today
        ];
    }
    
    /**
     * Calculate total distance traveled today based on completed orders
     */
    private function calculateTodayDistance($driverId, $today)
    {
        $completedOrders = DB::table('orders')
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->whereNotNull('pick_lat')
            ->whereNotNull('pick_lng')
            ->whereNotNull('drop_lat')
            ->whereNotNull('drop_lng')
            ->select('pick_lat', 'pick_lng', 'drop_lat', 'drop_lng')
            ->get();
        
        $totalDistance = 0;
        
        foreach ($completedOrders as $order) {
            $distance = $this->calculateDistance(
                $order->pick_lat,
                $order->pick_lng,
                $order->drop_lat,
                $order->drop_lng
            );
            
            // Convert from kilometers to meters
            $totalDistance += ($distance * 1000);
        }
        
        return $totalDistance;
    }
    
    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        return $distance;
    }
}