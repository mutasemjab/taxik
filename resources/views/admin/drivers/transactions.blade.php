@extends('layouts.admin')

@section('title', __('messages.Wallet Transactions'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Wallet Transactions') }}</h1>
        <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back') }}
        </a>
    </div>

    <!-- Top-up Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Top_Up_Balance_For') }}: {{ $driver->name }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-4">
                        @if($driver->photo)
                        <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}" alt="{{ $driver->name }}" class="img-profile rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                        <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No Image" class="img-profile rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        @endif
                    </div>
                    <div class="text-center">
                        <h5>{{ $driver->name }}</h5>
                        <p>{{ $driver->country_code }} {{ $driver->phone }}</p>
                        <h4>{{ __('messages.Current_Balance') }}: <span class="text-primary">{{ $driver->balance }}</span></h4>
                    </div>
                </div>
                <div class="col-md-8">
                    <form method="POST" action="{{ route('drivers.topUp', $driver->id) }}">
                        @csrf
                        <div class="form-group">
                            <label for="amount">{{ __('messages.Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" step="any" min="0.01" required>
                            @error('amount')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="note">{{ __('messages.Note') }}</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="3"></textarea>
                            @error('note')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus-circle"></i> {{ __('messages.Add_To_Balance') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Recent_Transactions') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.Amount') }}</th>
                            <th>{{ __('messages.Type') }}</th>
                            <th>{{ __('messages.Note') }}</th>
                            <th>{{ __('messages.Added_By') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($driver->walletTransactions()->latest()->take(100)->get() as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $transaction->amount }}</td>
                            <td>
                                @if($transaction->type_of_transaction == 1)
                                <span class="badge badge-success">{{ __('messages.Added') }}</span>
                                @else
                                <span class="badge badge-danger">{{ __('messages.Withdrawn') }}</span>
                                @endif
                            </td>
                            <td>{{ $transaction->note }}</td>
                            <td>{{ $transaction->admin ? $transaction->admin->name : 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection