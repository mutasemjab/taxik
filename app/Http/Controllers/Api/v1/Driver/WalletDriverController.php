<?php

namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Models\CardNumber;
use App\Models\CardUsage;
use App\Models\Order;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class WalletDriverController extends Controller
{
    use Responses;
  
    public function getTransactions(Request $request)
    {
        $driver = Auth::guard('driver-api')->user();
        
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|in:1,2', // 1 for add, 2 for withdrawal
            'per_page' => 'sometimes|integer|min:5|max:100',
            'sort_by' => 'sometimes|in:date,amount',
            'sort_direction' => 'sometimes|in:asc,desc'
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        $query = WalletTransaction::where('driver_id', $driver->id);
        $canDriverWithdrawal = Setting::where('key', 'can_driver_withdrawal')->value('value');
        $canDriverWithdrawalFlag = $canDriverWithdrawal == 1;

        // Filter by transaction type if provided
        if ($request->has('type')) {
            $query->where('type_of_transaction', $request->type);
        }
        
        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        if ($sortBy === 'date') {
            $sortBy = 'created_at';
        }
        
        $query->orderBy($sortBy, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $transactions = $query->paginate($perPage);
        
        $responseData = [
            'balance' => $driver->balance,
            'can_driver_withdrawal' => $canDriverWithdrawalFlag,
            'transactions' => $transactions,
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total()
            ]
        ];
        
        return $this->success_response('Driver wallet transactions retrieved successfully', $responseData);
    }

    public function useCard(Request $request)
    {        
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'type' => 'validation_error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get authenticated driver
        $driver = Auth::guard('driver-api')->user();
        if (!$driver) {
            return response()->json([
                'status' => false,
                'type' => 'driver_not_authenticated',
                'message' => __('messages.driver_not_authenticated')
            ], 401);
        }

        DB::beginTransaction();

        try {
            // Remove formatting from card number (remove dashes)
            $cardNumberClean = str_replace('-', '', $request->card_number);            
            
            // Find the card number
            $cardNumber = CardNumber::where('number', $cardNumberClean)->first();
            
            if (!$cardNumber) {
                return response()->json([
                    'status' => false,
                    'type' => 'card_number_not_found',
                    'message' => __('messages.invalid_card_number')
                ], 200);
            }

            // Check if card number is active
            if ($cardNumber->activate != CardNumber::ACTIVATE_ACTIVE) {
                return response()->json([
                    'status' => false,
                    'type' => 'card_number_inactive',
                    'message' => __('messages.card_number_inactive')
                ], 200);
            }

            // Check if card number is already used
            if ($cardNumber->status == CardNumber::STATUS_USED) {
                return response()->json([
                    'status' => false,
                    'type' => 'card_number_already_used',
                    'message' => __('messages.card_number_already_used')
                ], 200);
            }

            // Get the card details
            $card = $cardNumber->card;
            if (!$card) {
                return response()->json([
                    'status' => false,
                    'type' => 'card_not_found',
                    'message' => __('messages.card_not_found')
                ], 200);
            }

            // Add balance to driver wallet
            $walletTransaction = $driver->addBalance(
                $card->driver_recharge_amount,
                __('messages.card_usage_note', ['card_name' => $card->name, 'card_number' => $request->card_number])
            );

            // Mark card number as used
            $cardNumber->update(['status' => CardNumber::STATUS_USED]);

            // Record card usage
            $cardUsage = CardUsage::create([
                'driver_id' => $driver->id,
                'card_number_id' => $cardNumber->id,
                'wallet_transaction_id' => $walletTransaction->id,
                'used_at' => now(),
            ]);

            DB::commit();

            // Prepare response data
            $responseData = [
                'driver' => [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'balance' => $driver->fresh()->formatted_balance,
                    'previous_balance' => number_format($driver->balance - $card->driver_recharge_amount, 2),
                ],
                'card' => [
                    'id' => $card->id,
                    'name' => $card->name,
                    'price' => number_format($card->driver_recharge_amount, 2),
                    'pos' => $card->pos ? $card->pos->name : null,
                ],
                'card_number' => [
                    'id' => $cardNumber->id,
                    'number' => $request->card_number,
                    'formatted_number' => $cardNumber->formatted_number,
                ],
                'transaction' => [
                    'id' => $walletTransaction->id,
                    'amount' => $walletTransaction->formatted_amount,
                    'type' => $walletTransaction->type_text,
                    'note' => $walletTransaction->note,
                    'created_at' => $walletTransaction->created_at->format('Y-m-d H:i:s'),
                ],
                'usage' => [
                    'id' => $cardUsage->id,
                    'used_at' => $cardUsage->used_at->format('Y-m-d H:i:s'),
                    'location' => $cardUsage->location,
                ]
            ];

            return response()->json([
                'status' => true,
                'type' => 'card_used_successfully',
                'message' => __('messages.card_used_successfully'),
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('useCard: Exception occurred', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'driver_id' => $driver ? $driver->id : null,
                'card_number' => $request->card_number
            ]);
            
            return response()->json([
                'status' => false,
                'type' => 'server_error',
                'message' => __('messages.error_using_card') . ': ' . $e->getMessage()
            ], 500);
        }
    }

     public function addBalance(Request $request)
    {
        \Log::info('addBalance method called', ['request_data' => $request->all()]);
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:10000',
        ]);

        if ($validator->fails()) {
            \Log::warning('addBalance validation failed', ['errors' => $validator->errors()]);
            return $this->error_response($validator->errors(),[]);
        }

        // Get authenticated driver from token
        $driver = Auth::guard('driver-api')->user();
        if (!$driver) {
            \Log::error('addBalance: Driver not authenticated');
            return $this->error_response(__('messages.driver_not_authenticated'),[]);
        }

        \Log::info('addBalance: Driver authenticated', ['driver_id' => $driver->id, 'driver_name' => $driver->name]);

        DB::beginTransaction();

        try {
            $amount = $request->amount;
            $previousBalance = $driver->balance;
            
            \Log::info('addBalance: Starting balance addition', [
                'driver_id' => $driver->id,
                'amount_to_add' => $amount,
                'previous_balance' => $previousBalance
            ]);

            // Add balance to driver wallet
            $walletTransaction = $driver->addBalance(
                $amount,
                __('messages.balance_added_via_api')
            );
            
            \Log::info('addBalance: Balance added successfully', ['transaction_id' => $walletTransaction->id]);

            DB::commit();
            \Log::info('addBalance: Transaction committed successfully');

            // Prepare response data
            $responseData = [
                'driver' => [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'phone' => $driver->full_phone,
                    'previous_balance' => number_format($previousBalance, 2),
                    'current_balance' => $driver->fresh()->formatted_balance,
                    'added_amount' => number_format($amount, 2),
                ],
                'transaction' => [
                    'id' => $walletTransaction->id,
                    'amount' => $walletTransaction->formatted_amount,
                    'type' => $walletTransaction->type_text,
                    'note' => $walletTransaction->note,
                    'created_at' => $walletTransaction->created_at->format('Y-m-d H:i:s'),
                ]
            ];

            \Log::info('addBalance: Success response prepared', ['driver_id' => $driver->id, 'amount_added' => $amount]);
            return $this->success_response(
                __('messages.balance_added_successfully'),
                $responseData
            );

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('addBalance: Exception occurred', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'driver_id' => $driver ? $driver->id : null,
                'amount' => $request->amount
            ]);
            return $this->error_response(__('messages.error_adding_balance') . ': ' . $e->getMessage(),[]);
        }
    }


    public function addBalanceToUser(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->error_response($validator->errors()->first(), []);
        }

        // Get authenticated driver
        $driver = auth('driver-api')->user();

        // Get the order
        $order = Order::with('user')->find($request->order_id);

        // Validate order belongs to this driver
        if ($order->driver_id != $driver->id) {
            return $this->error_response('This order does not belong to you', 403);
        }

        // Validate order status is waiting_payment
        if ($order->status != 'waiting_payment') {
            return $this->error_response('Order status must be waiting_payment to add balance', 400);
        }

        // Validate payment method is cash
        if ($order->payment_method != 'cash') {
            return $this->error_response('This operation is only for cash payment orders', 400);
        }

        // Check if user exists
        if (!$order->user) {
            return $this->error_response('User not found for this order', 404);
        }

        // Check if driver has sufficient balance
        if ($driver->balance < $request->amount) {
            return $this->error_response('Insufficient balance in your wallet. Your balance: ' . $driver->balance . ', Required: ' . $request->amount, 400);
        }

        try {
            DB::beginTransaction();

            // Deduct amount from driver's wallet balance
            $driver->decrement('balance', $request->amount);

            // Add amount to user's wallet balance
            $order->user->increment('balance', $request->amount);

            // Create wallet transaction record for USER (add balance)
            $userTransaction = WalletTransaction::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'driver_id' => $driver->id,
                'admin_id' => null,
                'amount' => $request->amount,
                'type_of_transaction' => 1, // 1 = add
                'note' => $request->note ?? "Change returned by driver from order #{$order->number}"
            ]);

            // Create wallet transaction record for DRIVER (withdrawal)
            $driverTransaction = WalletTransaction::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'driver_id' => $driver->id,
                'admin_id' => null,
                'amount' => $request->amount,
                'type_of_transaction' => 2, // 2 = withdrawal
                'note' => $request->note ?? "Change paid to user from order #{$order->number}"
            ]);


            DB::commit();

            return $this->success_response('Balance transferred successfully', [
                'user_transaction' => [
                    'id' => $userTransaction->id,
                    'amount' => $userTransaction->amount,
                    'type' => 'credit',
                    'user_id' => $order->user_id,
                    'user_name' => $order->user->name,
                    'note' => $userTransaction->note
                ],
                'driver_transaction' => [
                    'id' => $driverTransaction->id,
                    'amount' => $driverTransaction->amount,
                    'type' => 'debit',
                    'driver_id' => $driver->id,
                    'driver_new_balance' => $driver->fresh()->balance,
                    'note' => $driverTransaction->note
                ],
                'order' => [
                    'id' => $order->id,
                    'number' => $order->number,
                    'status' => $order->status,
                    'status_payment' => $order->status_payment
                ],
                'transaction_date' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error_response('Failed to transfer balance: ' . $e->getMessage(), 500);
        }
    }
}