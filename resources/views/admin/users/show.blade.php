@extends('layouts.admin')

@section('title', __('messages.View_User'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.View_User') }}</h1>
        <div>
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- User Profile -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Profile') }}</h6>
                </div>
                <div class="card-body text-center">
                    @if($user->photo)
                    <img src="{{ asset('assets/admin/uploads/' . $user->photo) }}" alt="{{ $user->name }}" class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                    <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @endif
                    <h4 class="font-weight-bold">{{ $user->name }}</h4>
                    <p class="text-muted mb-1">{{ $user->country_code }} {{ $user->phone }}</p>
                    @if($user->email)
                    <p class="text-muted mb-1">{{ $user->email }}</p>
                    @endif
                    <div class="mt-3">
                        @if($user->activate == 1)
                        <span class="badge badge-success px-3 py-2">{{ __('messages.Active') }}</span>
                        @else
                        <span class="badge badge-danger px-3 py-2">{{ __('messages.Inactive') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Quick_Stats') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-wallet text-success"></i> {{ __('messages.Balance') }}</span>
                            <strong class="text-success">{{ number_format($user->balance, 2) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span><i class="fas fa-credit-card text-info"></i> {{ __('messages.App_Credit') }}</span>
                            <strong class="text-info">{{ number_format($user->app_credit, 2) }}</strong>
                        </div>
                        @if($user->app_credit_orders_remaining > 0)
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-shopping-cart"></i> {{ $user->app_credit_orders_remaining }} {{ __('messages.Orders_Remaining') }}
                        </small>
                        <small class="text-muted d-block">
                            <i class="fas fa-coins"></i> {{ number_format($user->app_credit_amount_per_order, 2) }} {{ __('messages.Per_Order') }}
                        </small>
                        @endif
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-shopping-bag text-primary"></i> {{ __('messages.Total_Orders') }}</span>
                            <strong class="text-primary">{{ $user->orders_count }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- User Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.User_Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('messages.ID') }}</th>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Name') }}</th>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Phone') }}</th>
                                    <td>{{ $user->country_code }} {{ $user->phone }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Email') }}</th>
                                    <td>{{ $user->email ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Balance') }}</th>
                                    <td><strong class="text-success">{{ number_format($user->balance, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.App_Credit') }}</th>
                                    <td>
                                        <strong class="text-info">{{ number_format($user->app_credit, 2) }}</strong>
                                        @if($user->app_credit_orders_remaining > 0)
                                        <br><small class="text-muted">
                                            ({{ $user->app_credit_orders_remaining }} {{ __('messages.Orders_Remaining') }}, 
                                            {{ number_format($user->app_credit_amount_per_order, 2) }} {{ __('messages.Per_Order') }})
                                        </small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Referral_Code') }}</th>
                                    <td>{{ $user->referral_code ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.SOS_Phone') }}</th>
                                    <td>{{ $user->sos_phone ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Status') }}</th>
                                    <td>
                                        @if($user->activate == 1)
                                        <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                        @else
                                        <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Created_At') }}</th>
                                    <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Updated_At') }}</th>
                                    <td>{{ $user->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-shopping-bag"></i> {{ __('messages.Orders') }} ({{ $user->orders_count }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.Order_Number') }}</th>
                            <th>{{ __('messages.Service') }}</th>
                            <th>{{ __('messages.Driver') }}</th>
                            <th>{{ __('messages.Total_Price_After_Discount') }}</th>
                            <th>{{ __('messages.Payment_Method') }}</th>
                            <th>{{ __('messages.Payment_Status') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->number }}</strong>
                            </td>
                            <td>{{ $order->service->name ?? __('messages.N/A') }}</td>
                            <td>
                                @if($order->driver)
                                    {{ $order->driver->name }}
                                @else
                                    <span class="text-muted">{{ __('messages.Not_Assigned') }}</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format($order->total_price_after_discount, 2) }}</strong>
                                @if($order->discount_value > 0)
                                    <br><small class="text-success">
                                        <i class="fas fa-tag"></i> -{{ number_format($order->discount_value, 2) }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($order->payment_method == 'cash')
                                    <span class="badge badge-success">
                                        <i class="fas fa-money-bill-wave"></i> {{ __('messages.Cash') }}
                                    </span>
                                @elseif($order->payment_method == 'visa')
                                    <span class="badge badge-primary">
                                        <i class="fas fa-credit-card"></i> {{ __('messages.Visa') }}
                                    </span>
                                @elseif($order->payment_method == 'wallet')
                                    <span class="badge badge-info">
                                        <i class="fas fa-wallet"></i> {{ __('messages.Wallet') }}
                                    </span>
                                @elseif($order->payment_method == 'app_credit')
                                    <span class="badge badge-warning">
                                        <i class="fas fa-coins"></i> {{ __('messages.App_Credit') }}
                                    </span>
                                @endif
                                @if($order->is_hybrid_payment)
                                    <br><small class="badge badge-secondary mt-1">
                                        <i class="fas fa-exchange-alt"></i> {{ __('messages.Hybrid') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($order->status_payment == 'paid')
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> {{ __('messages.Paid') }}
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> {{ __('messages.Pending') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $order->getStatusClass() }}">
                                    {{ $order->getStatusText() }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $order->created_at->format('Y-m-d') }}</small><br>
                                <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info" title="{{ __('messages.View') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-shopping-bag fa-3x mb-3 d-block"></i>
                                {{ __('messages.No_Orders_Found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
            <div class="mt-3">
                {{ $orders->appends(['wallet_page' => request('wallet_page'), 'credit_page' => request('credit_page')])->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Wallet Transactions Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-wallet"></i> {{ __('messages.Wallet_Transactions') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Type') }}</th>
                            <th>{{ __('messages.Amount') }}</th>
                            <th>{{ __('messages.Order_Number') }}</th>
                            <th>{{ __('messages.Admin') }}</th>
                            <th>{{ __('messages.Note') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($walletTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>
                                @if($transaction->type_of_transaction == 1)
                                    <span class="badge badge-success">
                                        <i class="fas fa-plus-circle"></i> {{ __('messages.Added') }}
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fas fa-minus-circle"></i> {{ __('messages.withdrawals') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong class="{{ $transaction->type_of_transaction == 1 ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type_of_transaction == 1 ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                                </strong>
                            </td>
                            <td>
                                @if($transaction->order)
                                    <a href="{{ route('orders.show', $transaction->order_id) }}" class="text-primary">
                                        <i class="fas fa-external-link-alt"></i> {{ $transaction->order->number }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ __('messages.N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->admin)
                                    <small>{{ $transaction->admin->name }}</small>
                                @else
                                    <span class="text-muted">{{ __('messages.System') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->note)
                                    <small>{{ $transaction->note }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $transaction->created_at->format('Y-m-d') }}</small><br>
                                <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-wallet fa-3x mb-3 d-block"></i>
                                {{ __('messages.No_data') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($walletTransactions->hasPages())
            <div class="mt-3">
                {{ $walletTransactions->appends(['orders_page' => request('orders_page'), 'credit_page' => request('credit_page')])->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- App Credit Transactions Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">
                <i class="fas fa-credit-card"></i> {{ __('messages.App_Credit_Transactions') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Type') }}</th>
                            <th>{{ __('messages.Amount') }}</th>
                            <th>{{ __('messages.Remaining') }}</th>
                            <th>{{ __('messages.amount_per_order') }}</th>
                            <th>{{ __('messages.Order_Number') }}</th>
                            <th>{{ __('messages.Admin') }}</th>
                            <th>{{ __('messages.Note') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appCreditTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>
                                @if($transaction->type_of_transaction == 1)
                                    <span class="badge badge-success">
                                        <i class="fas fa-plus-circle"></i> {{ __('messages.Add') }}
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fas fa-minus-circle"></i> {{ __('messages.Withdrawal') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong class="{{ $transaction->type_of_transaction == 1 ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type_of_transaction == 1 ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                                </strong>
                            </td>
                            <td>
                                @if($transaction->orders_remaining_after !== null)
                                    <span class="badge badge-secondary">
                                        {{ $transaction->orders_remaining_before }} 
                                        <i class="fas fa-arrow-right"></i> 
                                        {{ $transaction->orders_remaining_after }}
                                    </span>
                                @else
                                    <span class="text-muted">{{ __('messages.N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->amount_per_order)
                                    <strong>{{ number_format($transaction->amount_per_order, 2) }}</strong>
                                @else
                                    <span class="text-muted">{{ __('messages.N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->order)
                                    <a href="{{ route('orders.show', $transaction->order_id) }}" class="text-primary">
                                        <i class="fas fa-external-link-alt"></i> {{ $transaction->order->number }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ __('messages.N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->admin)
                                    <small>{{ $transaction->admin->name }}</small>
                                @else
                                    <span class="text-muted">{{ __('messages.System') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->note)
                                    <small>{{ $transaction->note }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $transaction->created_at->format('Y-m-d') }}</small><br>
                                <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-credit-card fa-3x mb-3 d-block"></i>
                                {{ __('messages.No_data') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($appCreditTransactions->hasPages())
            <div class="mt-3">
                {{ $appCreditTransactions->appends(['orders_page' => request('orders_page'), 'wallet_page' => request('wallet_page')])->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection