<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\WithdrawalRequest;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WithdrawalRequestUserController extends Controller
{
    use Responses;

  public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
        
           $user = auth('user-api')->user();
        
            // Check if user has enough balance
            if ($user->balance < $request->amount) {
                return $this->error_response('Insufficient balance',[]);
            }
            // Create withdrawal request
            WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
            ]);
          return $this->success_response('Withdrawal request submitted successfully',[]);
       
    }
}
