<?php


namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\WithdrawalRequest;
use App\Traits\Responses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WithdrawalRequestDriverController extends Controller
{
    use Responses;

    public function requestWithdrawal(Request $request)
    {
        // Get language from header (default to 'en' if not provided)
        $lang = $request->header('lang', 'en');

        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:5',
                'regex:/^[0-9]*[05]$/', // Must be multiple of 5
            ],
        ]);

        $driver = auth('driver-api')->user();

        // Check if today is Friday
        if (now()->dayOfWeek !== Carbon::FRIDAY) {
            $message = $lang === 'ar'
                ? 'طلبات السحب متاحة فقط يوم الجمعة'
                : 'Withdrawal requests are only allowed on Fridays';
            return $this->error_response($message, []);
        }

        // Check if driver already made a withdrawal request this Friday
        $existingRequest = WithdrawalRequest::where('driver_id', $driver->id)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($existingRequest) {
            $message = $lang === 'ar'
                ? 'لقد قمت بالفعل بتقديم طلب سحب اليوم'
                : 'You have already submitted a withdrawal request today';
            return $this->error_response($message, []);
        }

        // Check if amount is multiple of 5
        if ($request->amount % 5 != 0) {
            $message = $lang === 'ar'
                ? 'المبلغ يجب أن يكون من مضاعفات 5'
                : 'Amount must be a multiple of 5';
            return $this->error_response($message, []);
        }

        // Check if driver has enough balance (must keep at least 5 after withdrawal)
        $remainingBalance = $driver->balance - $request->amount;

        if ($remainingBalance < 5) {
            $message = $lang === 'ar'
                ? 'يجب أن يبقى في رصيدك 5 على الأقل بعد السحب'
                : 'You must keep at least 5 in your balance after withdrawal';
            return $this->error_response($message, []);
        }

        // Check if driver has enough balance
        if ($driver->balance < $request->amount) {
            $message = $lang === 'ar'
                ? 'رصيد غير كافٍ'
                : 'Insufficient balance';
            return $this->error_response($message, []);
        }

        // Create withdrawal request
        WithdrawalRequest::create([
            'driver_id' => $driver->id,
            'amount' => $request->amount,
        ]);

        $message = $lang === 'ar'
            ? 'تم تقديم طلب السحب بنجاح'
            : 'Withdrawal request submitted successfully';

        return $this->success_response($message, []);
    }
}
