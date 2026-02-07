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
        $totalPriceBeforeDiscount = $order->total_price_before_discount;
        $discountValue = $order->discount_value;
        $finalPrice = $order->total_price_after_discount;

        $commissionData = $this->getServiceCommission($order->service_id, $totalPriceBeforeDiscount);
        $adminCommission = $commissionData['admin_commission'];
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

            case PaymentMethod::AppCredit: // ✅ NEW
                $this->processAppCreditPayment($order, $driver, $finalPrice, $driverBaseEarning, $discountValue, $paymentDetails);
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
     * ✅ Process App Credit Payment - يخصم المبلغ المحدد والباقي نقدي
     */
    private function processAppCreditPayment($order, $driver, $finalPrice, $driverBaseEarning, $discountValue, &$paymentDetails)
    {
        $user = $order->user;

        // ========== Step 1: التحقق من رصيد التطبيق ==========
        $appCreditEnabled = DB::table('settings')
            ->where('key', 'enable_app_credit_distribution_system')
            ->value('value') == 1;

        if (!$appCreditEnabled) {
            throw new \Exception("App credit system is not enabled");
        }

        $availableAppCreditPerOrder = $user->getAvailableAppCreditForOrder();

        if ($availableAppCreditPerOrder <= 0) {
            throw new \Exception("No app credit available for this order");
        }

        // ========== Step 2: حساب المبلغ من رصيد التطبيق والنقدي ==========
        // ✅ نخصم فقط المبلغ المتاح (0.5 JD مثلاً)، والباقي نقدي
        $amountFromAppCredit = min($availableAppCreditPerOrder, $finalPrice);
        $cashAmount = max(0, $finalPrice - $amountFromAppCredit);

        $isHybridPayment = ($cashAmount > 0);

        // ========== Step 3: خصم من رصيد التطبيق ==========
        $ordersRemainingBefore = $user->app_credit_orders_remaining;

        DB::table('app_credit_transactions')->insert([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => $amountFromAppCredit,
            'type_of_transaction' => 2, // withdrawal
            'note' => $isHybridPayment
                ? "الدفع للطلب رقم {$order->id} من رصيد التطبيق ({$amountFromAppCredit} JD) والباقي نقدي ({$cashAmount} JD)"
                : "الدفع للطلب رقم {$order->id} من رصيد التطبيق ({$amountFromAppCredit} JD)",
            'orders_remaining_before' => $ordersRemainingBefore,
            'orders_remaining_after' => max(0, $ordersRemainingBefore - 1),
            'amount_per_order' => $user->app_credit_amount_per_order,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->decrement('app_credit', $amountFromAppCredit);

        $user->fresh()->decrementAppCreditOrdersRemaining();

        $paymentDetails['transactions_created'][] = [
            'type' => 'app_credit_deduction',
            'amount' => $amountFromAppCredit,
            'description' => 'Partial payment from app credit distribution system'
        ];

        // ✅ تحديث الطلب بمعلومات الدفع
        $order->app_credit_amount_used = $amountFromAppCredit;
        $order->wallet_amount_used = 0;
        $order->cash_amount_due = $cashAmount;
        $order->is_hybrid_payment = $isHybridPayment;
        $order->save();

        // ========== Step 4: تحويل المبلغ من رصيد التطبيق للسائق ==========
        if ($amountFromAppCredit > 0) {
            DB::table('wallet_transactions')->insert([
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'amount' => $amountFromAppCredit,
                'type_of_transaction' => 1, // addition
                'note' => "تحويل {$amountFromAppCredit} JD من رصيد تطبيق المستخدم للطلب رقم {$order->id}",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('drivers')
                ->where('id', $driver->id)
                ->increment('balance', $amountFromAppCredit);

            $paymentDetails['transactions_created'][] = [
                'type' => 'transfer_to_driver_from_app_credit',
                'amount' => $amountFromAppCredit,
                'description' => 'Amount transferred to driver from user app credit'
            ];
        }

        // ========== Step 5: خصم عمولة الإدارة من السعر الكلي ==========
        $adminCommission = $this->getServiceCommission($order->service_id, $order->total_price_before_discount)['admin_commission'];

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
            'description' => 'Admin commission deducted from driver wallet'
        ];

        // ========== Step 6: تعويض الخصم (الكوبون) ==========
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

        // ========== معلومات الدفع ==========
        $paymentDetails['payment_type'] = $isHybridPayment ? 'hybrid_app_credit_cash' : 'full_app_credit';
        $paymentDetails['app_credit_details'] = [
            'amount_used_from_app_credit' => $amountFromAppCredit,
            'cash_amount_required' => $cashAmount,
            'orders_remaining_before' => $ordersRemainingBefore,
            'orders_remaining_after' => $user->fresh()->app_credit_orders_remaining,
            'amount_per_order' => $user->app_credit_amount_per_order,
            'is_hybrid_payment' => $isHybridPayment
        ];

        if ($isHybridPayment) {
            $paymentDetails['cash_collection_required'] = true;
            $paymentDetails['cash_collection_details'] = [
                'amount' => $cashAmount,
                'note' => 'Driver must collect this amount in cash from user',
                'message_ar' => "يجب على السائق تحصيل {$cashAmount} JD نقداً من المستخدم",
                'message_en' => "Driver must collect JD {$cashAmount} cash from user"
            ];
        }

        $paymentDetails['user_final_balances'] = [
            'app_credit' => $user->fresh()->app_credit,
            'real_wallet' => $user->fresh()->balance,
            'app_credit_orders_remaining' => $user->fresh()->app_credit_orders_remaining
        ];

        $paymentDetails['driver_final_balance'] = $driver->fresh()->balance;

        Log::info("App credit payment processed for order {$order->id}", [
            'amount_from_app_credit' => $amountFromAppCredit,
            'cash_amount' => $cashAmount,
            'is_hybrid' => $isHybridPayment,
            'orders_remaining' => $user->fresh()->app_credit_orders_remaining
        ]);
    }

    /**
     * ✅ Update Wallet Payment (Remove App Credit Logic)
     */
     private function processWalletPayment($order, $driver, $finalPrice, $driverBaseEarning, $discountValue, &$paymentDetails)
    {
        $user = $order->user;
        $realWalletBalance = $user->balance;

        // ========== حساب المبلغ من المحفظة والنقدي ==========
        $amountFromWallet = min($realWalletBalance, $finalPrice); // خصم فقط ما هو متاح
        $cashAmount = max(0, $finalPrice - $amountFromWallet); // الباقي نقدي

        $isHybridPayment = ($cashAmount > 0);

        // ========== خصم من المحفظة الحقيقية ==========
        if ($amountFromWallet > 0) {
            DB::table('wallet_transactions')->insert([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'amount' => $amountFromWallet,
                'type_of_transaction' => 2, // withdrawal
                'note' => $isHybridPayment
                    ? "الدفع للطلب رقم {$order->id} من المحفظة ({$amountFromWallet} JD) والباقي نقدي ({$cashAmount} JD)"
                    : "الدفع للطلب رقم {$order->id} من المحفظة ({$amountFromWallet} JD)",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('users')
                ->where('id', $user->id)
                ->decrement('balance', $amountFromWallet);

            $paymentDetails['transactions_created'][] = [
                'type' => 'wallet_deduction',
                'amount' => $amountFromWallet,
                'description' => $isHybridPayment ? 'Partial payment from wallet' : 'Full payment from wallet'
            ];
        }

        // ✅ تحديث الطلب بمعلومات الدفع
        $order->app_credit_amount_used = 0;
        $order->wallet_amount_used = $amountFromWallet;
        $order->cash_amount_due = $cashAmount;
        $order->is_hybrid_payment = $isHybridPayment;
        $order->save();

        // ========== تحويل المبلغ من المحفظة للسائق ==========
        if ($amountFromWallet > 0) {
            DB::table('wallet_transactions')->insert([
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'amount' => $amountFromWallet,
                'type_of_transaction' => 1, // addition
                'note' => "تحويل {$amountFromWallet} JD من محفظة المستخدم للطلب رقم {$order->id}",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('drivers')
                ->where('id', $driver->id)
                ->increment('balance', $amountFromWallet);

            $paymentDetails['transactions_created'][] = [
                'type' => 'transfer_to_driver_from_wallet',
                'amount' => $amountFromWallet,
                'description' => 'Amount transferred to driver from user wallet'
            ];
        }

        // ========== خصم عمولة الإدارة من السعر الكلي ==========
        $adminCommission = $this->getServiceCommission($order->service_id, $order->total_price_before_discount)['admin_commission'];

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
            'description' => 'Admin commission deducted from driver wallet'
        ];

        // ========== تعويض الخصم (الكوبون) ==========
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

        // ========== معلومات الدفع ==========
        $paymentDetails['payment_type'] = $isHybridPayment ? 'hybrid_wallet_cash' : 'full_wallet';
        $paymentDetails['wallet_details'] = [
            'amount_used_from_wallet' => $amountFromWallet,
            'cash_amount_required' => $cashAmount,
            'is_hybrid_payment' => $isHybridPayment
        ];

        if ($isHybridPayment) {
            $paymentDetails['cash_collection_required'] = true;
            $paymentDetails['cash_collection_details'] = [
                'amount' => $cashAmount,
                'note' => 'Driver must collect this amount in cash from user',
                'message_ar' => "يجب على السائق تحصيل {$cashAmount} JD نقداً من المستخدم",
                'message_en' => "Driver must collect JD {$cashAmount} cash from user"
            ];
        }

        $paymentDetails['user_final_balance'] = $user->fresh()->balance;
        $paymentDetails['driver_final_balance'] = $driver->fresh()->balance;

        Log::info("Wallet payment processed for order {$order->id}", [
            'amount_from_wallet' => $amountFromWallet,
            'cash_amount' => $cashAmount,
            'is_hybrid' => $isHybridPayment
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
}
