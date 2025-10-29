<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
        use Responses;
    
        public function getTransactions(Request $request)
        {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|in:1,2', // 1 for add, 2 for withdrawal
                'per_page' => 'sometimes|integer|min:5|max:100',
                'sort_by' => 'sometimes|in:date,amount',
                'sort_direction' => 'sometimes|in:asc,desc'
            ]);
            
            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }
            
            $query = WalletTransaction::where('user_id', $user->id);
            
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
                'balance' => $user->balance,
                'transactions' => $transactions,
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total()
                ]
            ];
            
            return $this->success_response('User wallet transactions retrieved successfully', $responseData);
        }
        
        
        public function addBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:10000',
        ]);

        if ($validator->fails()) {
            return $this->error_response($validator->errors(),[]);
        }

        // Get authenticated driver from token
        $user = Auth::guard('user-api')->user();
        if (!$user) {
            return $this->error_response(__('messages.driver_not_authenticated'),[]);
        }

        DB::beginTransaction();

        try {
            $amount = $request->amount;
            $previousBalance = $user->balance;

            // Add balance to driver wallet
            $walletTransaction = $user->addBalance(
                $amount,
                __('messages.balance_added_via_api')
            );

            DB::commit();

            // Prepare response data
            $responseData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'previous_balance' => number_format($previousBalance, 2),
                    'current_balance' => $user->fresh()->formatted_balance,
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

            return $this->success_response(
                __('messages.balance_added_successfully'),
                $responseData
            );

        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response(__('messages.error_adding_balance') . ': ' . $e->getMessage(),[]);
        }
    }
    
}