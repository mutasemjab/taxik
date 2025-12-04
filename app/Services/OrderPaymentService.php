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
        $totalPrice = $order->total_price_after_discount;

        // Get commission details from service
        $commissionData = $this->getServiceCommission($order->service_id, $totalPrice);
        $adminCommission = $commissionData['admin_commission'];
        $driverEarning = $totalPrice - $adminCommission;

        $paymentDetails = [
            'payment_method' => $order->payment_method->value,
            'payment_method_text' => $order->getPaymentMethodText(),
            'total_price' => $totalPrice,
            'admin_commission_type' => $commissionData['type_text'],
            'admin_commission_value' => $commissionData['commission_value'],
            'admin_commission_amount' => $adminCommission,
            'driver_earning' => $driverEarning,
            'transactions_created' => []
        ];

        // Get driver from order if not provided
        if (!$driver && $order->driver_id) {
            $driver = $order->driver;
        }

        if (!$driver) {
            throw new \Exception("Driver information not found for order #{$order->id}");
        }

        // Process payment based on enum value
        switch ($order->payment_method) {
            case PaymentMethod::Cash:
                $this->processCashPayment($order, $driver, $adminCommission, $paymentDetails);
                break;

            case PaymentMethod::Visa:
                $this->processVisaPayment($order, $driver, $driverEarning, $paymentDetails);
                break;

            case PaymentMethod::Wallet:
                $this->processWalletPayment($order, $driver, $totalPrice, $driverEarning, $paymentDetails);
                break;

            default:
                throw new \Exception("Invalid payment method: " . $order->payment_method->value);
        }

        return $paymentDetails;
    }

    /**
     * Process cash payment - deduct admin commission from driver wallet
     */
    private function processCashPayment($order, $driver, $adminCommission, &$paymentDetails)
    {
        // Deduct admin commission from driver's wallet
        DB::table('wallet_transactions')->insert([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'amount' => $adminCommission,
            'type_of_transaction' => 2, // withdrawal
            'note' => "خصم عمولة الإدارة للدفع النقدي - الطلب رقم {$order->id}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update driver's wallet balance
        DB::table('drivers')
            ->where('id', $driver->id)
            ->decrement('balance', $adminCommission);

        $paymentDetails['transactions_created'][] = [
            'type' => 'driver_commission_deduction',
            'amount' => $adminCommission,
            'description' => 'Admin commission deducted from driver wallet for cash payment'
        ];

        Log::info("Cash payment processed - Admin commission {$adminCommission} deducted from driver {$driver->id} for order {$order->id}");
    }

    /**
     * Process visa/card payment - add driver earning to driver wallet
     */
    private function processVisaPayment($order, $driver, $driverEarning, &$paymentDetails)
    {
        // Add driver earning to driver's wallet
        DB::table('wallet_transactions')->insert([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'amount' => $driverEarning,
            'type_of_transaction' => 1, // addition
            'note' => "أرباح السائق من الدفع عبر فيزا - الطلب رقم {$order->id}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update driver's wallet balance
        DB::table('drivers')
            ->where('id', $driver->id)
            ->increment('balance', $driverEarning);

        $paymentDetails['transactions_created'][] = [
            'type' => 'driver_earning_addition',
            'amount' => $driverEarning,
            'description' => 'Driver earning added to wallet from visa payment'
        ];

        Log::info("Visa payment processed - Driver earning {$driverEarning} added to driver {$driver->id} for order {$order->id}");
    }

    /**
     * Process wallet payment - deduct from user wallet and add to driver wallet
     */
    private function processWalletPayment($order, $driver, $totalPrice, $driverEarning, &$paymentDetails)
    {
        $user = $order->user;

        // Check if user has sufficient balance
        if ($user->balance < $totalPrice) {
            throw new \Exception("Insufficient user wallet balance. Required: {$totalPrice}, Available: {$user->balance}");
        }

        // Deduct total price from user's wallet
        DB::table('wallet_transactions')->insert([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => $totalPrice,
            'type_of_transaction' => 2, // withdrawal
            'note' => "الدفع للطلب رقم {$order->id} عبر المحفظة",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update user's balance
        DB::table('users')
            ->where('id', $user->id)
            ->decrement('balance', $totalPrice);

        // Add driver earning to driver's wallet
        DB::table('wallet_transactions')->insert([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'amount' => $driverEarning,
            'type_of_transaction' => 1, // addition
            'note' => "أرباح السائق من الدفع عبر المحفظة - الطلب رقم {$order->id}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update driver's wallet balance
        DB::table('drivers')
            ->where('id', $driver->id)
            ->increment('balance', $driverEarning);

        $paymentDetails['transactions_created'][] = [
            'type' => 'user_payment_deduction',
            'amount' => $totalPrice,
            'description' => 'Total price deducted from user wallet'
        ];

        $paymentDetails['transactions_created'][] = [
            'type' => 'driver_earning_addition',
            'amount' => $driverEarning,
            'description' => 'Driver earning added to wallet from user payment'
        ];

        $paymentDetails['user_remaining_balance'] = $user->balance - $totalPrice;

        Log::info("Wallet payment processed - {$totalPrice} deducted from user {$user->id}, {$driverEarning} added to driver {$driver->id} for order {$order->id}");
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
