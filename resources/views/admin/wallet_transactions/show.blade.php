@extends('layouts.admin')

@section('title', __('messages.View_Transaction'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.View_Transaction') }}</h1>
        <a href="{{ route('wallet_transactions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
        </a>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <!-- Transaction Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 text-white {{ $transaction->type_of_transaction == 1 ? 'bg-success' : 'bg-danger' }}">
                    <h6 class="m-0 font-weight-bold">{{ $transaction->getTransactionTypeText() }}</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="p-4">
                            <h1 class="text-{{ $transaction->type_of_transaction == 1 ? 'success' : 'danger' }} font-weight-bold">
                                {{ $transaction->getFormattedAmount() }}
                            </h1>
                            <div class="text-muted">
                                {{ $transaction->created_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6>{{ __('messages.Entity') }}</h6>
                        @if($transaction->user_id)
                            <div class="d-flex align-items-center">
                                <span class="badge badge-info mr-2">{{ __('messages.User') }}</span>
                                <span>
                                    <a href="{{ route('users.show', $transaction->user_id) }}">
                                        {{ $transaction->user->name ?? 'N/A' }}
                                    </a>
                                    @if($transaction->user && $transaction->user->phone)
                                        <small class="text-muted ml-2">{{ $transaction->user->phone }}</small>
                                    @endif
                                </span>
                            </div>
                        @elseif($transaction->driver_id)
                            <div class="d-flex align-items-center">
                                <span class="badge badge-primary mr-2">{{ __('messages.Driver') }}</span>
                                <span>
                                    <a href="{{ route('drivers.show', $transaction->driver_id) }}">
                                        {{ $transaction->driver->name ?? 'N/A' }}
                                    </a>
                                    @if($transaction->driver && $transaction->driver->phone)
                                        <small class="text-muted ml-2">{{ $transaction->driver->phone }}</small>
                                    @endif
                                </span>
                            </div>
                        @else
                            {{ __('messages.Unknown') }}
                        @endif
                    </div>

                    <div class="mb-3">
                        <h6>{{ __('messages.Created_By') }}</h6>
                        <p>{{ $transaction->admin->name ?? __('messages.System') }}</p>
                    </div>

                    @if($transaction->note)
                    <div class="mb-3">
                        <h6>{{ __('messages.Note') }}</h6>
                        <div class="bg-light p-3 rounded">
                            {{ $transaction->note }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <!-- Entity Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Entity_Details') }}</h6>
                </div>
                <div class="card-body">
                    @if($transaction->user_id && $transaction->user)
                        <div class="row">
                            <div class="col-md-6 text-center mb-4">
                                @if($transaction->user->photo)
                                    <img src="{{ asset('assets/admin/uploads/' . $transaction->user->photo) }}" alt="{{ $transaction->user->name }}" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                @endif
                                <h5 class="mt-3">{{ $transaction->user->name }}</h5>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ __('messages.ID') }}
                                        <span>{{ $transaction->user->id }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ __('messages.Phone') }}
                                        <span>{{ $transaction->user->phone }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ __('messages.Current_Balance') }}
                                        <span class="badge badge-primary px-3 py-2">{{ $transaction->user->balance }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('wallet_transactions.userTransactions', $transaction->user_id) }}" class="btn btn-info">
                                <i class="fas fa-list"></i> {{ __('messages.View_All_User_Transactions') }}
                            </a>
                        </div>
                    @elseif($transaction->driver_id && $transaction->driver)
                        <div class="row">
                            <div class="col-md-6 text-center mb-4">
                                @if($transaction->driver->photo)
                                    <img src="{{ asset('assets/admin/uploads/' . $transaction->driver->photo) }}" alt="{{ $transaction->driver->name }}" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                @endif
                                <h5 class="mt-3">{{ $transaction->driver->name }}</h5>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ __('messages.ID') }}
                                        <span>{{ $transaction->driver->id }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ __('messages.Phone') }}
                                        <span>{{ $transaction->driver->phone }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ __('messages.Current_Balance') }}
                                        <span class="badge badge-primary px-3 py-2">{{ $transaction->driver->balance }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('wallet_transactions.driverTransactions', $transaction->driver_id) }}" class="btn btn-info">
                                <i class="fas fa-list"></i> {{ __('messages.View_All_Driver_Transactions') }}
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            {{ __('messages.Entity_Not_Available') }}
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Transaction Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Transaction_Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('messages.ID') }}</th>
                                    <td>{{ $transaction->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Amount') }}</th>
                                    <td class="{{ $transaction->type_of_transaction == 1 ? 'text-success' : 'text-danger' }} font-weight-bold">
                                        {{ $transaction->getFormattedAmount() }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Transaction_Type') }}</th>
                                    <td>
                                        <span class="badge badge-{{ $transaction->type_of_transaction == 1 ? 'success' : 'danger' }} px-3 py-2">
                                            {{ $transaction->getTransactionTypeText() }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Created_At') }}</th>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Created_By') }}</th>
                                    <td>{{ $transaction->admin->name ?? __('messages.System') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection