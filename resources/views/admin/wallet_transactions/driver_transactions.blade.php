@extends('layouts.admin')

@section('title', __('messages.Driver_Transactions'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Driver_Transactions') }}: {{ $driver->name }}</h1>
        <div>
            <a href="{{ route('wallet_transactions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('messages.Add_New_Transaction') }}
            </a>
            <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-info">
                <i class="fas fa-user"></i> {{ __('messages.View_Driver_Profile') }}
            </a>
            <a href="{{ route('wallet_transactions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
            </a>
        </div>
    </div>

    <!-- Driver Details Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Driver_Details') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 text-center">
                    @if($driver->photo)
                        <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}" alt="{{ $driver->name }}" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                    @else
                        <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                    @endif
                </div>
                <div class="col-md-5">
                    <h5 class="font-weight-bold">{{ $driver->name }}</h5>
                    <p class="mb-1"><i class="fas fa-phone text-primary"></i> {{ $driver->phone }}</p>
                    @if($driver->email)
                        <p class="mb-1"><i class="fas fa-envelope text-primary"></i> {{ $driver->email }}</p>
                    @endif
                    @if($driver->option)
                        <p class="mb-1"><i class="fas fa-car text-primary"></i> {{ $driver->option->name }}</p>
                    @endif
                </div>
                <div class="col-md-5 text-center">
                    <div class="h-100 d-flex flex-column justify-content-center">
                        <h4>{{ __('messages.Current_Balance') }}</h4>
                        <h2 class="text-primary">{{ $driver->balance }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Total_Deposits') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $transactions->where('type_of_transaction', 1)->sum('amount') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                {{ __('messages.Total_Withdrawals') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $transactions->where('type_of_transaction', 2)->sum('amount') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Total_Transactions') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $transactions->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Transaction_History') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.Amount') }}</th>
                            <th>{{ __('messages.Type') }}</th>
                            <th>{{ __('messages.Note') }}</th>
                            <th>{{ __('messages.Created_By') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td class="{{ $transaction->type_of_transaction == 1 ? 'text-success' : 'text-danger' }} font-weight-bold">
                                {{ $transaction->getFormattedAmount() }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $transaction->type_of_transaction == 1 ? 'success' : 'danger' }}">
                                    {{ $transaction->getTransactionTypeText() }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $transaction->note ?? __('messages.No_Note') }}</small>
                            </td>
                            <td>
                                {{ $transaction->admin->name ?? __('messages.System') }}
                            </td>
                            <td>
                                <a href="{{ route('wallet_transactions.show', $transaction->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[1, "desc"]]
        });
    });
</script>
@endsection