<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    // هذا الـ Model لا يحتاج جدول في قاعدة البيانات
    // هو فقط للتقارير والحسابات
    
    /**
     * تقرير شامل للسائقين من تاريخ لتاريخ
     */
    public static function getDriversFinancialReport($startDate, $endDate, $driverId = null)
    {
        $query = Driver::with(['registrationPayments', 'walletTransactions', 'withdrawalRequests']);
        
        if ($driverId) {
            $query->where('id', $driverId);
        }
        
        $drivers = $query->get()->map(function ($driver) use ($startDate, $endDate) {
            
            // 1. رسوم التسجيل
            $registrationPayments = $driver->registrationPayments()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
            
            $totalPaidOnRegistration = $registrationPayments->sum('total_paid');
            $totalKeptFromRegistration = $registrationPayments->sum('amount_kept');
            $totalAddedToWalletFromRegistration = $registrationPayments->sum('amount_added_to_wallet');
            
            // 2. شحن البطاقات
            $cardUsages = CardUsage::where('driver_id', $driver->id)
                ->whereBetween('used_at', [$startDate, $endDate])
                ->with(['cardNumber.card.pos'])
                ->get();
            
            $totalCardsUsed = $cardUsages->count();
            $totalCardsPurchaseValue = $cardUsages->sum(function ($usage) {
                return $usage->cardNumber->card->price ?? 0;
            });
            
            $totalRechargedFromCards = $cardUsages->sum(function ($usage) {
                return $usage->cardNumber->card->driver_recharge_amount ?? 0;
            });
            
            $totalPosCommissionFromCards = $cardUsages->sum(function ($usage) {
                $card = $usage->cardNumber->card;
                if ($card) {
                    return ($card->price * ($card->pos_commission_percentage ?? 0) / 100);
                }
                return 0;
            });
            
            $totalNetFromCards = $totalCardsPurchaseValue - $totalPosCommissionFromCards;
            
            // 3. المعاملات المالية (الإضافات والسحوبات)
            $walletTransactions = $driver->walletTransactions()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
            
            $totalDeposits = $walletTransactions->where('type_of_transaction', 1)->sum('amount');
            $totalWithdrawals = $walletTransactions->where('type_of_transaction', 2)->sum('amount');
            
            // 4. طلبات السحب
            $withdrawalRequests = $driver->withdrawalRequests()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
            
            $pendingWithdrawals = $withdrawalRequests->where('status', 1)->sum('amount');
            $approvedWithdrawals = $withdrawalRequests->where('status', 2)->sum('amount');
            $rejectedWithdrawals = $withdrawalRequests->where('status', 3)->sum('amount');
            
            // 5. الحسابات الإجمالية
            $totalRevenue = $totalKeptFromRegistration + $totalNetFromCards;
            
            return [
                'driver_id' => $driver->id,
                'driver_name' => $driver->name,
                'driver_phone' => $driver->phone,
                'current_balance' => $driver->balance,
                
                // بيانات التسجيل
                'registration' => [
                    'total_paid' => $totalPaidOnRegistration,
                    'amount_kept' => $totalKeptFromRegistration,
                    'amount_added_to_wallet' => $totalAddedToWalletFromRegistration,
                ],
                
                // بيانات البطاقات
                'cards' => [
                    'total_cards_used' => $totalCardsUsed,
                    'total_purchase_value' => $totalCardsPurchaseValue,
                    'total_pos_commission' => $totalPosCommissionFromCards,
                    'total_net_from_cards' => $totalNetFromCards,
                    'total_recharged_to_driver' => $totalRechargedFromCards,
                ],
                
                // معاملات المحفظة
                'wallet_transactions' => [
                    'total_deposits' => $totalDeposits,
                    'total_withdrawals' => $totalWithdrawals,
                    'net_transactions' => $totalDeposits - $totalWithdrawals,
                ],
                
                // طلبات السحب
                'withdrawal_requests' => [
                    'pending' => $pendingWithdrawals,
                    'approved' => $approvedWithdrawals,
                    'rejected' => $rejectedWithdrawals,
                ],
                
                // الإجمالي
                'total_revenue_from_driver' => $totalRevenue,
            ];
        });
        
        // حساب الإجماليات الكلية
        $summary = [
            'total_drivers' => $drivers->count(),
            'total_revenue' => $drivers->sum('total_revenue_from_driver'),
            'total_registration_revenue' => $drivers->sum('registration.amount_kept'),
            'total_cards_revenue' => $drivers->sum('cards.total_net_from_cards'),
            'total_pos_commission' => $drivers->sum('cards.total_pos_commission'),
            'total_added_to_wallets' => $drivers->sum('registration.amount_added_to_wallet') + 
                                        $drivers->sum('cards.total_recharged_to_driver'),
            'total_withdrawals' => $drivers->sum('wallet_transactions.total_withdrawals'),
        ];
        
        return [
            'drivers' => $drivers,
            'summary' => $summary,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ];
    }
    
    /**
     * تقرير نقاط البيع
     */
    public static function getPOSFinancialReport($startDate, $endDate, $posId = null)
    {
        $query = POS::query();
        
        if ($posId) {
            $query->where('id', $posId);
        }
        
        $posPoints = $query->get()->map(function ($pos) use ($startDate, $endDate) {
            
            $cardsUsed = CardUsage::whereBetween('used_at', [$startDate, $endDate])
                ->whereHas('cardNumber.card', function ($q) use ($pos) {
                    $q->where('pos_id', $pos->id);
                })
                ->with(['cardNumber.card'])
                ->get();
            
            $totalCardsSold = $cardsUsed->count();
            $totalSalesValue = $cardsUsed->sum(function ($usage) {
                return $usage->cardNumber->card->price ?? 0;
            });
            
            $totalCommission = $cardsUsed->sum(function ($usage) {
                $card = $usage->cardNumber->card;
                if ($card) {
                    return ($card->price * ($card->pos_commission_percentage ?? 0) / 100);
                }
                return 0;
            });
            
            $netDueToAdmin = $totalSalesValue - $totalCommission;
            
            return [
                'pos_id' => $pos->id,
                'pos_name' => $pos->name,
                'pos_phone' => $pos->phone,
                'total_cards_sold' => $totalCardsSold,
                'total_sales_value' => $totalSalesValue,
                'total_commission' => $totalCommission,
                'net_due_to_admin' => $netDueToAdmin,
            ];
        });
        
        return [
            'pos_points' => $posPoints,
            'summary' => [
                'total_pos' => $posPoints->count(),
                'total_cards_sold' => $posPoints->sum('total_cards_sold'),
                'total_sales_value' => $posPoints->sum('total_sales_value'),
                'total_commission_paid' => $posPoints->sum('total_commission'),
                'total_net_revenue' => $posPoints->sum('net_due_to_admin'),
            ]
        ];
    }
    
    /**
     * تقرير إجمالي شامل
     */
    public static function getOverallSummary($startDate, $endDate)
    {
        $driversReport = self::getDriversFinancialReport($startDate, $endDate);
        $posReport = self::getPOSFinancialReport($startDate, $endDate);
        
        return [
            'drivers_summary' => $driversReport['summary'],
            'pos_summary' => $posReport['summary'],
            'total_revenue' => $driversReport['summary']['total_revenue'] + 
                               $posReport['summary']['total_net_revenue'],
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ];
    }
}