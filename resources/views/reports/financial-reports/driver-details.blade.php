@extends('layouts.admin')

@section('title', __('messages.Driver_Financial_Details'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-circle"></i> {{ __('messages.Driver_Financial_Details') }}
        </h1>
        <a href="{{ route('financial-reports.index') }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" 
           class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_Report') }}
        </a>
    </div>

    <!-- Driver Info Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-info-circle"></i> {{ __('messages.Driver_Information') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="text-center">
                        @if($driver->photo)
                            <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}" 
                                 alt="{{ $driver->name }}" 
                                 class="img-fluid rounded-circle mb-3" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 120px; height: 120px;">
                                <i class="fas fa-user fa-3x text-white"></i>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-user"></i> {{ __('messages.Name') }}:</strong> {{ $driver->name }}</p>
                            <p><strong><i class="fas fa-phone"></i> {{ __('messages.Phone') }}:</strong> {{ $driver->phone }}</p>
                            <p><strong><i class="fas fa-envelope"></i> {{ __('messages.Email') }}:</strong> {{ $driver->email ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-wallet"></i> {{ __('messages.Current_Balance') }}:</strong> 
                                <span class="badge badge-info badge-lg p-2">
                                    {{ number_format($driver->balance, 2) }} {{ __('messages.JD') }}
                                </span>
                            </p>
                            <p><strong><i class="fas fa-toggle-on"></i> {{ __('messages.Status') }}:</strong> 
                                @if($driver->activate == 1)
                                    <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                @endif
                            </p>
                            <p><strong><i class="fas fa-calendar-plus"></i> {{ __('messages.Registration_Date') }}:</strong> 
                                {{ $driver->created_at->format('Y-m-d') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Info -->
    <div class="alert alert-info">
        <i class="fas fa-calendar-alt"></i> 
        <strong>{{ __('messages.Report_Period') }}:</strong> 
        {{ \Carbon\Carbon::parse(request('start_date'))->format('Y-m-d') }} 
        {{ __('messages.To') }} 
        {{ \Carbon\Carbon::parse(request('end_date'))->format('Y-m-d') }}
    </div>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        {{ __('messages.Registration_Revenue') }}
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($details['registration']['amount_kept'], 2) }} {{ __('messages.JD') }}
                    </div>
                    <small class="text-muted">
                        {{ __('messages.From') }} {{ number_format($details['registration']['total_paid'], 2) }} {{ __('messages.paid') }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        {{ __('messages.Cards_Revenue') }}
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($details['cards']['total_net_from_cards'], 2) }} {{ __('messages.JD') }}
                    </div>
                    <small class="text-muted">
                        {{ $details['cards']['total_cards_used'] }} {{ __('messages.cards_used') }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        {{ __('messages.Total_Withdrawals') }}
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($details['wallet_transactions']['total_withdrawals'], 2) }} {{ __('messages.JD') }}
                    </div>
                    <small class="text-muted">
                        {{ __('messages.From_wallet') }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        {{ __('messages.Total_Revenue') }}
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($details['total_revenue_from_driver'], 2) }} {{ __('messages.JD') }}
                    </div>
                    <small class="text-muted">
                        {{ __('messages.Your_profit_from_driver') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Payments -->
    @if($registrationPayments->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-file-invoice-dollar"></i> {{ __('messages.Registration_Payments') }}
                <span class="badge badge-primary ml-2">{{ $registrationPayments->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.Total_Paid') }}</th>
                            <th>{{ __('messages.Amount_Kept') }}</th>
                            <th>{{ __('messages.Added_To_Wallet') }}</th>
                            <th>{{ __('messages.Note') }}</th>
                            <th>{{ __('messages.Admin') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrationPayments as $payment)
                        <tr>
                            <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ number_format($payment->total_paid, 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-success font-weight-bold">
                                {{ number_format($payment->amount_kept, 2) }} {{ __('messages.JD') }}
                            </td>
                            <td>{{ number_format($payment->amount_added_to_wallet, 2) }} {{ __('messages.JD') }}</td>
                            <td>{{ $payment->note ?? '-' }}</td>
                            <td>{{ $payment->admin->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td>{{ __('messages.Total') }}</td>
                            <td>{{ number_format($registrationPayments->sum('total_paid'), 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-success">{{ number_format($registrationPayments->sum('amount_kept'), 2) }} {{ __('messages.JD') }}</td>
                            <td>{{ number_format($registrationPayments->sum('amount_added_to_wallet'), 2) }} {{ __('messages.JD') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Card Usages -->
    @if($cardUsages->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-credit-card"></i> {{ __('messages.Recharge_Cards_History') }}
                <span class="badge badge-primary ml-2">{{ $cardUsages->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.Card_Number') }}</th>
                            <th>{{ __('messages.Card_Price') }}</th>
                            <th>{{ __('messages.Recharged_Amount') }}</th>
                            <th>{{ __('messages.POS') }}</th>
                            <th>{{ __('messages.POS_Commission') }}</th>
                            <th>{{ __('messages.Net_Revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cardUsages as $usage)
                        @php
                            $card = $usage->cardNumber->card;
                            $commission = ($card->price * ($card->pos_commission_percentage ?? 0) / 100);
                            $netRevenue = $card->price - $commission;
                        @endphp
                        <tr>
                            <td>{{ $usage->used_at->format('Y-m-d H:i') }}</td>
                            <td><code>{{ $usage->cardNumber->number }}</code></td>
                            <td>{{ number_format($card->price, 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-info font-weight-bold">
                                {{ number_format($card->driver_recharge_amount, 2) }} {{ __('messages.JD') }}
                            </td>
                            <td>{{ $card->pos->name ?? '-' }}</td>
                            <td class="text-danger">{{ number_format($commission, 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-success font-weight-bold">{{ number_format($netRevenue, 2) }} {{ __('messages.JD') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="2">{{ __('messages.Total') }}</td>
                            <td>{{ number_format($details['cards']['total_purchase_value'], 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-info">{{ number_format($details['cards']['total_recharged_to_driver'], 2) }} {{ __('messages.JD') }}</td>
                            <td>-</td>
                            <td class="text-danger">{{ number_format($details['cards']['total_pos_commission'], 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-success">{{ number_format($details['cards']['total_net_from_cards'], 2) }} {{ __('messages.JD') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Wallet Transactions -->
    @if($walletTransactions->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-exchange-alt"></i> {{ __('messages.Wallet_Transactions') }}
                <span class="badge badge-primary ml-2">{{ $walletTransactions->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.Type') }}</th>
                            <th>{{ __('messages.Amount') }}</th>
                            <th>{{ __('messages.Note') }}</th>
                            <th>{{ __('messages.Admin') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($walletTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($transaction->type_of_transaction == 1)
                                    <span class="badge badge-success">
                                        <i class="fas fa-plus"></i> {{ __('messages.Deposit') }}
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fas fa-minus"></i> {{ __('messages.Withdrawal') }}
                                    </span>
                                @endif
                            </td>
                            <td class="{{ $transaction->type_of_transaction == 1 ? 'text-success' : 'text-danger' }} font-weight-bold">
                                {{ number_format($transaction->amount, 2) }} {{ __('messages.JD') }}
                            </td>
                            <td>{{ $transaction->note ?? '-' }}</td>
                            <td>{{ $transaction->admin->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Withdrawal Requests -->
    @if($withdrawalRequests->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-hand-holding-usd"></i> {{ __('messages.Withdrawal_Requests') }}
                <span class="badge badge-primary ml-2">{{ $withdrawalRequests->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.Amount') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Note') }}</th>
                            <th>{{ __('messages.Processed_By') }}</th>
                            <th>{{ __('messages.Processed_At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawalRequests as $request)
                        <tr>
                            <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                            <td class="font-weight-bold">{{ number_format($request->amount, 2) }} {{ __('messages.JD') }}</td>
                            <td>
                                @if($request->status == 1)
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> {{ __('messages.Pending') }}
                                    </span>
                                @elseif($request->status == 2)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> {{ __('messages.Approved') }}
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times"></i> {{ __('messages.Rejected') }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $request->note ?? '-' }}</td>
                            <td>{{ $request->admin->name ?? '-' }}</td>
                            <td>{{ $request->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Print and Export Buttons -->
    <div class="text-center mb-4">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print"></i> {{ __('messages.Print') }}
        </button>
        <a href="{{ route('financial-reports.index') }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" 
           class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_Report') }}
        </a>
    </div>

</div>
@endsection

@section('script')
<style>
@media print {
    .btn, .sidebar, .topbar, .navbar {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
}
</style>
@endsection