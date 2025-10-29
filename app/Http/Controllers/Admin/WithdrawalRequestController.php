<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WithdrawalRequestController  extends Controller
{
   public function index()
    {
        $pendingRequests = WithdrawalRequest::with(['user', 'driver'])
            ->where('status', 1)
            ->orderBy('created_at', 'asc')
            ->paginate(15);
            
        return view('admin.withdrawals.index', compact('pendingRequests'));
    }
    
  public function history($id)
    {
        // Determine if this is a driver or user
        $isDriver = Driver::where('id', $id)->exists();
        $isUser = User::where('id', $id)->exists();
        
        if ($isDriver) {
            $person = Driver::findOrFail($id);
            $processedRequests = WithdrawalRequest::where('driver_id', $id)
                ->whereIn('status', [2, 3])
                ->orderBy('updated_at', 'desc')
                ->paginate(15);
        } elseif ($isUser) {
            $person = User::findOrFail($id);
            $processedRequests = WithdrawalRequest::where('user_id', $id)
                ->whereIn('status', [2, 3])
                ->orderBy('updated_at', 'desc')
                ->paginate(15);
        } else {
            return back()->with('error', 'Invalid user or driver ID');
        }
        
        return view('admin.withdrawals.history', compact('processedRequests', 'person'));
    }
    
    public function approve($id)
    {
        $request = WithdrawalRequest::findOrFail($id);
        
        if ($request->status != 1) {
            return back()->with('error', 'This request has already been processed');
        }
        
        // Begin transaction
        \DB::beginTransaction();
        
        try {
            // Update request status
            $request->status = 2; // Approved
            $request->admin_id =auth()->user()->id;
            $request->save();
            
            // Create wallet transaction
            $transaction = new WalletTransaction();
            
            if ($request->user_id) {
                $user = User::find($request->user_id);
                
                if ($user->balance < $request->amount) {
                    return back()->with('error', 'User has insufficient balance');
                }
                
                $user->balance -= $request->amount;
                $user->save();
                
                $transaction->user_id = $user->id;
            } else {
                $driver = Driver::find($request->driver_id);
                
                if ($driver->balance < $request->amount) {
                    return back()->with('error', 'Driver has insufficient balance');
                }
                
                $driver->balance -= $request->amount;
                $driver->save();
                
                $transaction->driver_id = $driver->id;
            }
            
            $transaction->admin_id = auth()->user()->id;
            $transaction->amount = $request->amount;
            $transaction->type_of_transaction = 2; // withdrawal
            $transaction->note = 'Withdrawal request #' . $request->id . ' approved';
            $transaction->save();
            
            \DB::commit();
            
            return back()->with('success', 'Withdrawal request approved successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    
    public function reject(Request $request, $id)
    {
        $withdrawalRequest = WithdrawalRequest::findOrFail($id);
        
        if ($withdrawalRequest->status != 1) {
            return back()->with('error', 'This request has already been processed');
        }
        
        $request->validate([
            'note' => 'required|string|max:255',
        ]);
        
        $withdrawalRequest->status = 3; // Rejected
        $withdrawalRequest->note = $request->note;
        $withdrawalRequest->admin_id = auth()->user()->id;
        $withdrawalRequest->save();
        
        return back()->with('success', 'Withdrawal request rejected');
    }
}