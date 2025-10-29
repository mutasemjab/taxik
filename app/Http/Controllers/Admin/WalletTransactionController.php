<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class WalletTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions = WalletTransaction::with(['user', 'driver', 'admin'])->orderBy('created_at', 'desc')->get();
        return view('admin.wallet_transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        $drivers = Driver::all();
        return view('admin.wallet_transactions.create', compact('users', 'drivers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|in:user,driver',
            'entity_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'type_of_transaction' => 'required|in:1,2',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('wallet_transactions.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Prepare transaction data
        $transactionData = [
            'amount' => $request->amount,
            'type_of_transaction' => $request->type_of_transaction,
            'note' => $request->note,
            'admin_id' => Auth::id(), // Current logged in admin
        ];

        // Set the appropriate entity (user or driver)
        if ($request->entity_type == 'user') {
            $user = User::findOrFail($request->entity_id);
            $transactionData['user_id'] = $user->id;
            $transactionData['driver_id'] = null;
            
            // Update user balance
            if ($request->type_of_transaction == 1) {
                // Add to balance
                $user->balance += $request->amount;
            } else {
                // Withdraw from balance
                if ($user->balance < $request->amount) {
                    return redirect()
                        ->route('wallet_transactions.create')
                        ->with('error', __('messages.Insufficient_Balance'))
                        ->withInput();
                }
                $user->balance -= $request->amount;
            }
            $user->save();
        } else {
            $driver = Driver::findOrFail($request->entity_id);
            $transactionData['driver_id'] = $driver->id;
            $transactionData['user_id'] = null;
            
            // Update driver balance
            if ($request->type_of_transaction == 1) {
                // Add to balance
                $driver->balance += $request->amount;
            } else {
                // Withdraw from balance
                if ($driver->balance < $request->amount) {
                    return redirect()
                        ->route('wallet_transactions.create')
                        ->with('error', __('messages.Insufficient_Balance'))
                        ->withInput();
                }
                $driver->balance -= $request->amount;
            }
            $driver->save();
        }

        // Create the transaction
        WalletTransaction::create($transactionData);

        return redirect()
            ->route('wallet_transactions.index')
            ->with('success', __('messages.Transaction_Created_Successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = WalletTransaction::with(['user', 'driver', 'admin'])->findOrFail($id);
        return view('admin.wallet_transactions.show', compact('transaction'));
    }

    /**
     * Filter transactions by entity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|in:all,user,driver',
            'entity_id' => 'nullable|required_if:entity_type,user,driver',
            'transaction_type' => 'nullable|in:all,1,2',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('wallet_transactions.index')
                ->withErrors($validator);
        }

        $query = WalletTransaction::with(['user', 'driver', 'admin']);

        // Filter by entity type and ID
        if ($request->entity_type == 'user' && $request->entity_id) {
            $query->where('user_id', $request->entity_id);
        } elseif ($request->entity_type == 'driver' && $request->entity_id) {
            $query->where('driver_id', $request->entity_id);
        } elseif ($request->entity_type == 'user') {
            $query->whereNotNull('user_id');
        } elseif ($request->entity_type == 'driver') {
            $query->whereNotNull('driver_id');
        }

        // Filter by transaction type
        if ($request->transaction_type && $request->transaction_type != 'all') {
            $query->where('type_of_transaction', $request->transaction_type);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get the filtered transactions
        $transactions = $query->orderBy('created_at', 'desc')->get();
        
        // Get users and drivers for the filter dropdowns
        $users = User::all();
        $drivers = Driver::all();

        return view('admin.wallet_transactions.index', compact('transactions', 'users', 'drivers'));
    }

    /**
     * Show user transactions.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userTransactions($id)
    {
        $user = User::findOrFail($id);
        $transactions = WalletTransaction::with(['admin'])
            ->where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.wallet_transactions.user_transactions', compact('transactions', 'user'));
    }

    /**
     * Show driver transactions.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function driverTransactions($id)
    {
        $driver = Driver::findOrFail($id);
        $transactions = WalletTransaction::with(['admin'])
            ->where('driver_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.wallet_transactions.driver_transactions', compact('transactions', 'driver'));
    }
}