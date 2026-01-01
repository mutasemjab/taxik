<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderPaymentService
{
    /**
     * Update order status to delivered and process payment
     */
    public function markAsDeliveredAndProcessPayment(Order $order, $driver = null)
    {
        try {
            // Update order status to delivered
            $order->status = OrderStatus::Delivered;
            $order->status_payment = StatusPayment::Paid;

            // Complete trip timing if not already done
            if ($order->trip_started_at && !$order->trip_completed_at) {
                $tripCompletedAt = now();
                $order->trip_completed_at = $tripCompletedAt;
                $order->actual_trip_duration_minutes = $order->trip_started_at->diffInMinutes($tripCompletedAt);
            }

            // Process payment
            $paymentDetails = $this->processPayment($order, $driver);

            // Save order changes
            $order->save();

            return [
                'success' => true,
                'order' => $order,
                'payment_details' => $paymentDetails
            ];
        } catch (\Exception $e) {
            Log::error('Error processing order delivery and payment: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process payment based on payment method when order is delivered
     */
    public function processPayment($order, $driver = null)
    {
        // Price breakdown:
        $totalPriceBeforeDiscount = $order->total_price_before_discount; // e.g., 2 JD
        $discountValue = $order->discount_value; // e.g., 1 JD (coupon discount)
        $finalPrice = $order->total_price_after_discount; // e.g., 1 JD (what user pays)

        // CRITICAL: Admin commission calculated from FULL PRICE (before discount)
        $commissionData = $this->getServiceCommission($order->service_id, $totalPriceBeforeDiscount);
        $adminCommission = $commissionData['admin_commission'];

        // Driver base earning (from full price - commission)
        $driverBaseEarning = $totalPriceBeforeDiscount - $adminCommission;

        $paymentDetails = [
            'payment_method' => $order->payment_method->value,
            'payment_method_text' => $order->getPaymentMethodText(),
            'total_price_before_discount' => $totalPriceBeforeDiscount,
            'discount_value' => $discountValue,
            'total_price_after_discount' => $finalPrice,
            'admin_commission_type' => $commissionData['type_text'],
            'admin_commission_value' => $commissionData['commission_value'],
            'admin_commission_amount' => $adminCommission,
            'driver_base_earning' => $driverBaseEarning,
            'discount_compensation' => $discountValue > 0 ? $discountValue : 0,
            'driver_total_earning' => $driverBaseEarning,
            'transactions_created' => []
        ];

        // Get driver from order if not provided
        if (!$driver && $order->driver_id) {
            $driver = $order->driver;
        }

        if (!$driver) {
            throw new \Exception("Driver information not found for order #{$order->id}");
        }

        // Process payment based on method
        switch ($order->payment_method) {
            case PaymentMethod::Cash:
                $this->processCashPayment($order, $driver, $adminCommission, $discountValue, $paymentDetails);
                break;

            case PaymentMethod::Visa:
                $this->processVisaPayment($order, $driver, $driverBaseEarning, $discountValue, $paymentDetails);
                break;

            case PaymentMethod::Wallet:
                $this->processWalletPayment($order, $driver, $finalPrice, $driverBaseEarning, $discountValue, $paymentDetails);
                break;

            default:
                throw new \Exception("Invalid payment method: " . $order->payment_method->value);
        }

        return $paymentDetails;
    }

    private function processCashPayment($order, $driver, $adminCommission, $discountValue, &$paymentDetails)
    {
        // Step 1: Deduct admin commission from driver's wallet
        DB::table('wallet_transactions')->insert([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'amount' => $adminCommission,
            'type_of_transaction' => 2, // withdrawal
            'note' => "خصم عمولة الإدارة للدفع النقدي - الطلب رقم {$order->id}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('drivers')
            ->where('id', $driver->id)
            ->decrement('balance', $adminCommission);

        $paymentDetails['transactions_created'][] = [
            'type' => 'driver_commission_deduction',
            'amount' => $adminCommission,
            'description' => 'Admin commission deducted from driver wallet'
        ];

        // Step 2: Add discount compensation to driver's wallet
        if ($discountValue > 0) {
            DB::table('wallet_transactions')->insert([
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'amount' => $discountValue,
                'type_of_transaction' => 1, // addition
                'note' => "تعويض الكوبون للطلب رقم {$order->id}",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('drivers')
                ->where('id', $driver->id)
                ->increment('balance', $discountValue);

            $paymentDetails['transactions_created'][] = [
                'type' => 'discount_compensation',
                'amount' => $discountValue,
                'description' => 'Coupon discount compensation added to driver wallet'
            ];
        }

        Log::info("Cash payment processed for order {$order->id}: Commission {$adminCommission}, Discount compensation {$discountValue}");
    }

    /**
     * Process visa/card payment
     * - User pays DISCOUNTED price via card (already processed)
     * - Platform adds driver's earning to wallet
     * - Platform compensates driver for discount
     */
    private function processVisaPayment($order, $driver, $driverBaseEarning, $discountValue, &$paymentDetails)
    {
        // Step 1: Add driver earning to driver's wallet
        DB::table('wallet_transactions')->insert([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'amount' => $driverBaseEarning,
            'type_of_transaction' => 1, // addition
            'note' => "أرباح السائق من الدفع عبر فيزا - الطلب رقم {$order->id}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('drivers')
            ->where('id', $driver->id)
            ->increment('balance', $driverBaseEarning);

        $paymentDetails['transactions_created'][] = [
            'type' => 'driver_earning_addition',
            'amount' => $driverBaseEarning,
            'description' => 'Driver earning added to wallet from visa payment'
        ];

        // Step 2: Add discount compensation to driver's wallet
        if ($discountValue > 0) {
            DB::table('wallet_transactions')->insert([
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'amount' => $discountValue,
                'type_of_transaction' => 1, // addition
                'note' => "تعويض الكوبون للطلب رقم {$order->id}",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('drivers')
                ->where('id', $driver->id)
                ->increment('balance', $discountValue);

            $paymentDetails['transactions_created'][] = [
                'type' => 'discount_compensation',
                'amount' => $discountValue,
                'description' => 'Coupon discount compensation added to driver wallet'
            ];
        }

        Log::info("Visa payment processed for order {$order->id}: Driver earning {$driverBaseEarning}, Discount compensation {$discountValue}");
    }

    /**
     * Process wallet payment
     * - User pays DISCOUNTED price from wallet
     * - Platform adds driver's earning to wallet  
     * - Platform compensates driver for discount
     */
   /**
 * Process wallet payment (including hybrid wallet + cash)
 */
private function processWalletPayment($order, $driver, $finalPrice, $driverBaseEarning, $discountValue, &$paymentDetails)
{
    $user = $order->user;
    $userBalance = $user->balance;

    // Determine if this is hybrid payment or full wallet payment
    $isHybridPayment = $userBalance < $finalPrice;
    $walletAmount = $isHybridPayment ? $userBalance : $finalPrice;
    $cashAmount = $isHybridPayment ? ($finalPrice - $walletAmount) : 0;

    // Update order with payment breakdown
    $order->is_hybrid_payment = $isHybridPayment;
    $order->wallet_amount_used = $walletAmount;
    $order->cash_amount_due = $cashAmount;
    $order->save();

    if ($isHybridPayment) {
        $paymentDetails['payment_type'] = 'hybrid_wallet_cash';
        $paymentDetails['wallet_amount'] = $walletAmount;
        $paymentDetails['cash_amount'] = $cashAmount;
        $paymentDetails['message'] = "Hybrid payment: JD {$walletAmount} from wallet + JD {$cashAmount} cash";
        
        Log::info("Order {$order->id}: Hybrid payment - Wallet: {$walletAmount}, Cash: {$cashAmount}");
    } else {
        $paymentDetails['payment_type'] = 'full_wallet';
        $paymentDetails['wallet_amount'] = $walletAmount;
        $paymentDetails['cash_amount'] = 0;
        
        Log::info("Order {$order->id}: Full wallet payment - Amount: {$walletAmount}");
    }

    // ========== STEP 1: Deduct available wallet amount from user ==========
    if ($walletAmount > 0) {
        DB::table('wallet_transactions')->insert([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => $walletAmount,
            'type_of_transaction' => 2, // withdrawal
            'note' => $isHybridPayment 
                ? "الدفع الجزئي للطلب رقم {$order->id} عبر المحفظة (دفع هجين)" 
                : "الدفع للطلب رقم {$order->id} عبر المحفظة",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->decrement('balance', $walletAmount);

        $paymentDetails['transactions_created'][] = [
            'type' => 'user_wallet_deduction',
            'amount' => $walletAmount,
            'description' => $isHybridPayment 
                ? 'Partial payment from user wallet (hybrid payment)'
                : 'Full payment from user wallet'
        ];
    }

    // ========== STEP 2: Transfer wallet amount to driver ==========
    if ($walletAmount > 0) {
        DB::table('wallet_transactions')->insert([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'amount' => $walletAmount,
            'type_of_transaction' => 1, // addition
            'note' => $isHybridPayment
                ? "تحويل المبلغ من محفظة المستخدم للطلب رقم {$order->id} (دفع هجين)"
                : "تحويل المبلغ من محفظة المستخدم للطلب رقم {$order->id}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('drivers')
            ->where('id', $driver->id)
            ->increment('balance', $walletAmount);

        $paymentDetails['transactions_created'][] = [
            'type' => 'wallet_transfer_to_driver',
            'amount' => $walletAmount,
            'description' => 'Wallet amount transferred to driver'
        ];
    }

    // ========== STEP 3: Calculate admin commission from FULL price ==========
    // Commission is based on total price BEFORE discount
    $totalPriceBeforeDiscount = $order->total_price_before_discount;
    $commissionData = $this->getServiceCommission($order->service_id, $totalPriceBeforeDiscount);
    $adminCommission = $commissionData['admin_commission'];

    // Deduct admin commission from driver's wallet
    DB::table('wallet_transactions')->insert([
        'order_id' => $order->id,
        'driver_id' => $driver->id,
        'amount' => $adminCommission,
        'type_of_transaction' => 2, // withdrawal
        'note' => "خصم عمولة الإدارة للطلب رقم {$order->id}",
        'created_at' => now(),
        'updated_at' => now()
    ]);

    DB::table('drivers')
        ->where('id', $driver->id)
        ->decrement('balance', $adminCommission);

    $paymentDetails['transactions_created'][] = [
        'type' => 'admin_commission_deduction',
        'amount' => $adminCommission,
        'description' => 'Admin commission deducted from driver wallet (calculated from full price before discount)'
    ];

    // ========== STEP 4: Add discount compensation to driver ==========
    if ($discountValue > 0) {
        DB::table('wallet_transactions')->insert([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'amount' => $discountValue,
            'type_of_transaction' => 1, // addition
            'note' => "تعويض الكوبون للطلب رقم {$order->id}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('drivers')
            ->where('id', $driver->id)
            ->increment('balance', $discountValue);

        $paymentDetails['transactions_created'][] = [
            'type' => 'discount_compensation',
            'amount' => $discountValue,
            'description' => 'Coupon discount compensation added to driver wallet'
        ];
    }

    // ========== STEP 5: Handle cash portion (if hybrid) ==========
    if ($isHybridPayment && $cashAmount > 0) {
        $paymentDetails['cash_collection_required'] = true;
        $paymentDetails['cash_collection_details'] = [
            'amount' => $cashAmount,
            'note' => 'Driver must collect this amount in cash from user',
            'message_ar' => "يجب على السائق تحصيل {$cashAmount} JD نقداً من المستخدم",
            'message_en' => "Driver must collect JD {$cashAmount} cash from user"
        ];

        // Deduct admin commission from cash portion as well
        $cashAdminCommission = ($cashAmount * $commissionData['commission_value']) / 
            ($commissionData['commission_type'] == 1 ? $totalPriceBeforeDiscount : 100);
        
        if ($cashAdminCommission > 0) {
            DB::table('wallet_transactions')->insert([
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'amount' => $cashAdminCommission,
                'type_of_transaction' => 2, // withdrawal
                'note' => "خصم عمولة الإدارة من الجزء النقدي للطلب رقم {$order->id}",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('drivers')
                ->where('id', $driver->id)
                ->decrement('balance', $cashAdminCommission);

            $paymentDetails['transactions_created'][] = [
                'type' => 'cash_portion_commission',
                'amount' => $cashAdminCommission,
                'description' => 'Admin commission from cash portion deducted from driver wallet'
            ];
        }
    }

    $paymentDetails['user_remaining_balance'] = $user->fresh()->balance;
    $paymentDetails['driver_final_balance'] = $driver->fresh()->balance;

    Log::info("Wallet payment processed for order {$order->id}", [
        'is_hybrid' => $isHybridPayment,
        'wallet_amount' => $walletAmount,
        'cash_amount' => $cashAmount,
        'admin_commission' => $adminCommission,
        'discount_compensation' => $discountValue
    ]);
}
    /**
     * Get service commission details (PUBLIC method for external use)
     */
    public function getServiceCommission($serviceId, $totalPrice)
    {
        $service = DB::table('services')->where('id', $serviceId)->first();

        if (!$service) {
            throw new \Exception("Service not found with ID: {$serviceId}");
        }

        $commissionValue = $service->admin_commision;
        $commissionType = $service->type_of_commision;

        if ($commissionType == 1) {
            // Fixed commission
            $adminCommission = $commissionValue;
        } else {
            // Percentage commission
            $adminCommission = ($totalPrice * $commissionValue) / 100;
        }

        return [
            'commission_value' => $commissionValue,
            'commission_type' => $commissionType,
            'type_text' => $commissionType == 1 ? 'fixed' : 'percentage',
            'admin_commission' => $adminCommission
        ];
    }

    /**
     * Determine if current time is morning or evening
     * Morning: before 18:00 (6 PM)
     * Evening: 18:00 (6 PM) and after
     */
    private function isEveningTime($dateTime = null)
    {
        $checkTime = $dateTime ?? now();
        $hour = $checkTime->format('H');
        return $hour >= 22 || $hour < 6;
    }

    /**
     * Get appropriate pricing fields based on time of day
     */
    public function getTimePeriodPricing($service, $dateTime = null)
    {
        $isEvening = $this->isEveningTime($dateTime);
        
        return [
            'period' => $isEvening ? 'evening' : 'morning',
            'start_price' => $isEvening ? $service->start_price_evening : $service->start_price_morning,
            'price_per_km' => $isEvening ? $service->price_per_km_evening : $service->price_per_km_morning,
        ];
    }

    
    /**
     * Calculate final price based on settings and trip duration
     * Includes in-trip waiting charges when pricing method is "both" (3)
     */
    // public function calculateFinalPrice($order)
    // {
    //     // Get pricing method from settings
    //     $pricingMethod = $this->getPricingMethod();

    //     $pricingDetails = [
    //         'pricing_method' => $this->getPricingMethodText($pricingMethod),
    //         'initial_estimated_price' => $order->total_price_before_discount,
    //         'initial_discount' => $order->discount_value,
    //         'price_updated' => false,
    //         'coupon_recalculated' => false,
    //     ];

    //     $newCalculatedPrice = $order->total_price_before_discount; // Default to original price
    //     $discountValue = $order->discount_value; // Default to existing discount

    //     if ($pricingMethod == 1 && $order->trip_started_at) {
    //         // Time-based pricing calculation with morning/evening rates
    //         $tripDurationMinutes = $order->trip_started_at->diffInMinutes(now());
            
    //         // Get pricing based on current time period
    //         $timePricing = $this->getTimePeriodPricing($order->service);
            
    //         $pricePerMinute = $timePricing['price_per_minute'];
    //         $startPrice = $timePricing['start_price'];
    //         $realPriceBasedOnTime = $tripDurationMinutes * $pricePerMinute;
    //         $newCalculatedPrice = $startPrice + $realPriceBasedOnTime;

    //         $pricingDetails = array_merge($pricingDetails, [
    //             'price_updated' => true,
    //             'time_period' => $timePricing['period'],
    //             'trip_duration_minutes' => $tripDurationMinutes,
    //             'price_per_minute' => $pricePerMinute,
    //             'service_start_price' => $startPrice,
    //             'time_based_price' => $realPriceBasedOnTime,
    //             'new_calculated_price' => $newCalculatedPrice,
    //         ]);

    //         // Recalculate coupon discount if order has a coupon and price changed
    //         if ($order->coupon_id && $newCalculatedPrice != $order->total_price_before_discount) {
    //             $coupon = $order->coupon;

    //             if ($coupon && $coupon->isValid()) {
    //                 // Check if new price still meets minimum amount requirement
    //                 if ($newCalculatedPrice >= $coupon->minimum_amount) {
    //                     // Recalculate discount based on new price
    //                     if ($coupon->discount_type == 1) { // Fixed amount
    //                         $discountValue = $coupon->discount;
    //                     } else { // Percentage
    //                         $discountValue = ($newCalculatedPrice * $coupon->discount) / 100;
    //                     }

    //                     // Ensure discount doesn't exceed total price
    //                     $discountValue = min($discountValue, $newCalculatedPrice);

    //                     $pricingDetails['coupon_recalculated'] = true;
    //                     $pricingDetails['coupon_valid_for_new_price'] = true;
    //                     $pricingDetails['new_discount_value'] = $discountValue;

    //                     Log::info("Coupon discount recalculated for order {$order->id}: old discount {$order->discount_value}, new discount {$discountValue}");
    //                 } else {
    //                     // New price doesn't meet minimum requirement, remove coupon discount
    //                     $discountValue = 0;
    //                     $pricingDetails['coupon_recalculated'] = true;
    //                     $pricingDetails['coupon_valid_for_new_price'] = false;
    //                     $pricingDetails['coupon_removed_reason'] = 'New price below minimum amount requirement';

    //                     Log::info("Coupon discount removed for order {$order->id} - new price {$newCalculatedPrice} below minimum {$coupon->minimum_amount}");
    //                 }
    //             } else {
    //                 // Coupon is no longer valid, remove discount
    //                 $discountValue = 0;
    //                 $pricingDetails['coupon_recalculated'] = true;
    //                 $pricingDetails['coupon_valid_for_new_price'] = false;
    //                 $pricingDetails['coupon_removed_reason'] = 'Coupon expired or inactive';

    //                 Log::info("Coupon discount removed for order {$order->id} - coupon no longer valid");
    //             }
    //         }
    //     } else {
    //         // Distance-based pricing (keep original price)
    //         $pricingDetails = array_merge($pricingDetails, [
    //             'new_calculated_price' => $newCalculatedPrice,
    //             'note' => $pricingMethod == 2
    //                 ? 'Price calculated based on distance, no time adjustment applied'
    //                 : 'Trip start time not found, using original price'
    //         ]);
    //     }

    //     // ========== ADD IN-TRIP WAITING CHARGES (for traffic stops, etc.) ==========
    //     // Only apply when pricing method is "both" (3) and mobile sent waiting minutes
    //     if ($pricingMethod == 3 && $order->in_trip_waiting_minutes > 0) {
    //         $inTripWaitingMinutes = $order->in_trip_waiting_minutes;
    //         $chargePerMinute = $order->service->waiting_charge_per_minute_when_order_active ?? 0;
            
    //         $inTripWaitingCharges = $inTripWaitingMinutes * $chargePerMinute;
            
    //         $pricingDetails['in_trip_waiting_charges'] = [
    //             'in_trip_waiting_minutes' => $inTripWaitingMinutes,
    //             'charge_per_minute' => $chargePerMinute,
    //             'total_charges' => round($inTripWaitingCharges, 2),
    //             'description' => 'Charges for stopped time during trip (traffic, lights, etc.)'
    //         ];
            
    //         // Add in-trip waiting charges to the calculated price
    //         $newCalculatedPrice += $inTripWaitingCharges;
    //         $pricingDetails['new_calculated_price'] = $newCalculatedPrice;
    //         $pricingDetails['price_includes_in_trip_waiting'] = true;
            
    //         Log::info("Order {$order->id}: In-trip waiting charges calculated", [
    //             'waiting_minutes' => $inTripWaitingMinutes,
    //             'charge_per_minute' => $chargePerMinute,
    //             'total_charges' => $inTripWaitingCharges
    //         ]);
    //     }
    //     // ========== END IN-TRIP WAITING CHARGES ==========

    //     // Calculate final price after discount
    //     $finalPrice = $newCalculatedPrice - $discountValue;

    //     $pricingDetails['final_discount_value'] = $discountValue;
    //     $pricingDetails['final_price'] = $finalPrice;

    //     // Calculate commission based on final price using service commission
    //     $priceCalculation = $this->calculateCommissionAndNetPrice($order->service_id, $finalPrice);

    //     $pricingDetails = array_merge($pricingDetails, [
    //         'commission_type' => $priceCalculation['commission_type'],
    //         'commission_value' => $priceCalculation['commission_value'],
    //         'admin_commission' => $priceCalculation['admin_commission'],
    //         'net_price_for_driver' => $priceCalculation['net_price_for_driver']
    //     ]);

    //     return $pricingDetails;
    // }

    // /**
    //  * Get pricing calculation method from settings
    //  * 1 = time-based, 2 = distance-based, 3 = both
    //  */
    // private function getPricingMethod()
    // {
    //     return $this->getSettingValue('calculate_price_depend_on_time_or_distance_or_both', 2);
    // }

    // /**
    //  * Get text representation of pricing method
    //  */
    // private function getPricingMethodText($method)
    // {
    //     switch ($method) {
    //         case 1:
    //             return 'time_based';
    //         case 2:
    //             return 'distance_based';
    //         case 3:
    //             return 'both_time_and_distance';
    //         default:
    //             return 'unknown';
    //     }
    // }


    // /**
    //  * Get setting value by key with default fallback
    //  */
    // private function getSettingValue($key, $default = 0)
    // {
    //     $setting = DB::table('settings')->where('key', $key)->first();
    //     return $setting ? $setting->value : $default;
    // }

 

    // /**
    //  * Calculate admin commission and driver net price using service commission
    //  */
    // private function calculateCommissionAndNetPrice($serviceId, $totalPrice)
    // {
    //     $commissionData = $this->getServiceCommission($serviceId, $totalPrice);
    //     $adminCommission = $commissionData['admin_commission'];
    //     $netPriceForDriver = $totalPrice - $adminCommission;

    //     return [
    //         'commission_type' => $commissionData['type_text'],
    //         'commission_value' => $commissionData['commission_value'],
    //         'admin_commission' => $adminCommission,
    //         'net_price_for_driver' => $netPriceForDriver
    //     ];
    // }
}
