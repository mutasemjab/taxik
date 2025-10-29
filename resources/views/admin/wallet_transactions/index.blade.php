@extends('layouts.admin')

@section('title', __('messages.Wallet_Transactions'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Wallet_Transactions') }}</h1>
        <a href="{{ route('wallet_transactions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_Transaction') }}
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Filter_Transactions') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('wallet_transactions.filter') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="entity_type">{{ __('messages.Entity_Type') }}</label>
                            <select class="form-control" id="entity_type" name="entity_type">
                                <option value="all" {{ request('entity_type') == 'all' ? 'selected' : '' }}>{{ __('messages.All') }}</option>
                                <option value="user" {{ request('entity_type') == 'user' ? 'selected' : '' }}>{{ __('messages.Users') }}</option>
                                <option value="driver" {{ request('entity_type') == 'driver' ? 'selected' : '' }}>{{ __('messages.Drivers') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3 entity-select user-select" style="display: {{ request('entity_type') == 'user' ? 'block' : 'none' }};">
                        <div class="form-group">
                            <label for="user_id">{{ __('messages.Select_User') }}</label>
                            <select class="form-control" id="user_id" name="entity_id">
                                <option value="">{{ __('messages.All_Users') }}</option>
                                @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('entity_id') == $user->id && request('entity_type') == 'user' ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->phone }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3 entity-select driver-select" style="display: {{ request('entity_type') == 'driver' ? 'block' : 'none' }};">
                        <div class="form-group">
                            <label for="driver_id">{{ __('messages.Select_Driver') }}</label>
                            <select class="form-control" id="driver_id" name="entity_id">
                                <option value="">{{ __('messages.All_Drivers') }}</option>
                                @foreach($drivers ?? [] as $driver)
                                <option value="{{ $driver->id }}" {{ request('entity_id') == $driver->id && request('entity_type') == 'driver' ? 'selected' : '' }}>
                                    {{ $driver->name }} ({{ $driver->phone }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="transaction_type">{{ __('messages.Transaction_Type') }}</label>
                            <select class="form-control" id="transaction_type" name="transaction_type">
                                <option value="all" {{ request('transaction_type') == 'all' ? 'selected' : '' }}>{{ __('messages.All_Types') }}</option>
                                <option value="1" {{ request('transaction_type') == '1' ? 'selected' : '' }}>{{ __('messages.Deposit') }}</option>
                                <option value="2" {{ request('transaction_type') == '2' ? 'selected' : '' }}>{{ __('messages.Withdrawal') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">{{ __('messages.Date_From') }}</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">{{ __('messages.Date_To') }}</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> {{ __('messages.Filter') }}
                        </button>
                        <a href="{{ route('wallet_transactions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> {{ __('messages.Reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Wallet_Transactions_List') }}</h6>
            <div>
                <span class="badge badge-success px-3 py-2 mr-2">
                    {{ __('messages.Total_Deposits') }}: 
                    {{ $transactions->where('type_of_transaction', 1)->sum('amount') }}
                </span>
                <span class="badge badge-danger px-3 py-2">
                    {{ __('messages.Total_Withdrawals') }}: 
                    {{ $transactions->where('type_of_transaction', 2)->sum('amount') }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.Entity') }}</th>
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
                            <td>
                                @if($transaction->user_id)
                                    <span class="badge badge-info">{{ __('messages.User') }}</span>
                                    <a href="{{ route('users.show', $transaction->user_id) }}">
                                        {{ $transaction->user->name ?? 'N/A' }}
                                    </a>
                                @elseif($transaction->driver_id)
                                    <span class="badge badge-primary">{{ __('messages.Driver') }}</span>
                                    <a href="{{ route('drivers.show', $transaction->driver_id) }}">
                                        {{ $transaction->driver->name ?? 'N/A' }}
                                    </a>
                                @else
                                    {{ __('messages.Unknown') }}
                                @endif
                            </td>
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
        
        // Handle entity type selection
        $('#entity_type').on('change', function() {
            $('.entity-select').hide();
            
            if ($(this).val() == 'user') {
                $('.user-select').show();
                $('#driver_id').val('');
            } else if ($(this).val() == 'driver') {
                $('.driver-select').show();
                $('#user_id').val('');
            } else {
                // All selected, clear both
                $('#user_id, #driver_id').val('');
            }
        });
        
        // Date validation
        $('#date_to').on('change', function() {
            var startDate = $('#date_from').val();
            var endDate = $(this).val();
            
            if (startDate && endDate && startDate > endDate) {
                alert("{{ __('messages.Date_Range_Error') }}");
                $(this).val('');
            }
        });
    });
</script>
@endsection