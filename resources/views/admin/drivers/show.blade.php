@extends('layouts.admin')

@section('title', __('messages.View_Driver'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.View_Driver') }}</h1>
        <div>
            <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
            </a>
            <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- Driver Profile -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Profile') }}</h6>
                </div>
                <div class="card-body text-center">
                    @if($driver->photo)
                    <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}" alt="{{ $driver->name }}" class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                    <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @endif
                    <h4 class="font-weight-bold">{{ $driver->name }}</h4>
                    <p class="text-muted mb-1">{{ $driver->country_code }} {{ $driver->phone }}</p>
                    @if($driver->email)
                    <p class="text-muted mb-1">{{ $driver->email }}</p>
                    @endif
                    <div class="mt-3">
                        @if($driver->activate == 1)
                        <span class="badge badge-success px-3 py-2">{{ __('messages.Active') }}</span>
                        @else
                        <span class="badge badge-danger px-3 py-2">{{ __('messages.Inactive') }}</span>
                        @endif
                    </div>
                    <div class="mt-2">
                        @if($driver->status == 1)
                        <span class="badge badge-success px-3 py-2">
                            <i class="fas fa-circle"></i> {{ __('messages.Online') }}
                        </span>
                        @else
                        <span class="badge badge-secondary px-3 py-2">
                            <i class="fas fa-circle"></i> {{ __('messages.Offline') }}
                        </span>
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
                            <strong class="text-success">{{ number_format($driver->balance, 2) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-shopping-bag text-primary"></i> {{ __('messages.Total_Orders') }}</span>
                            <strong class="text-primary">{{ $driver->orders_count }}</strong>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clock text-info"></i> {{ __('messages.Last_Login') }}</span>
                            <div class="text-right">
                                @if($driver->last_login)
                                    <small class="d-block">{{ $driver->last_login->diffForHumans() }}</small>
                                    <small class="text-muted">{{ $driver->last_login->format('Y-m-d H:i') }}</small>
                                @else
                                    <span class="badge badge-warning">{{ __('messages.Never_Logged_In') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Car Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Car_Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        @if($driver->photo_of_car)
                        <img src="{{ asset('assets/admin/uploads/' . $driver->photo_of_car) }}" alt="Car Photo" class="img-fluid rounded mb-3" style="max-height: 150px;">
                        @else
                        <div class="bg-light rounded py-5 mb-3">
                            <i class="fas fa-car fa-3x text-gray-300"></i>
                            <p class="mt-2 text-gray-500">{{ __('messages.No_Car_Photo') }}</p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="40%">{{ __('messages.Car_Model') }}</th>
                                    <td>{{ $driver->model ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Production_Year') }}</th>
                                    <td>{{ $driver->production_year ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Color') }}</th>
                                    <td>{{ $driver->color ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Plate_Number') }}</th>
                                    <td>{{ $driver->plate_number ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Passenger_Number') }}</th>
                                    <td>{{ $driver->passenger_number ?? __('messages.Not_Available') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Driver Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Driver_Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('messages.ID') }}</th>
                                    <td>{{ $driver->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Name') }}</th>
                                    <td>{{ $driver->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Phone') }}</th>
                                    <td>{{ $driver->country_code }} {{ $driver->phone }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Email') }}</th>
                                    <td>{{ $driver->email ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.SOS_Phone') }}</th>
                                    <td>{{ $driver->sos_phone ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Options') }}</th>
                                    <td>
                                        @if($driver->options->count() > 0)
                                            @foreach($driver->options as $option)
                                                <span class="badge badge-success m-1">
                                                    {{ $option->name }} 
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">{{ __('messages.No_Options') }}</span>
                                        @endif                         
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Balance') }}</th>
                                    <td><strong class="text-success">{{ number_format($driver->balance, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Referral_Code') }}</th>
                                    <td>{{ $driver->referral_code ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Status') }}</th>
                                    <td>
                                        @if($driver->activate == 1)
                                        <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                        @elseif($driver->activate == 2)
                                        <span class="badge badge-danger">{{ __('messages.Banned') }}</span>
                                        @elseif($driver->activate == 3)
                                        <span class="badge badge-warning">{{ __('messages.Waiting_Approve') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Online_Status') }}</th>
                                    <td>
                                        @if($driver->status == 1)
                                        <span class="badge badge-success">
                                            <i class="fas fa-circle"></i> {{ __('messages.Online') }}
                                        </span>
                                        @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-circle"></i> {{ __('messages.Offline') }}
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Last_Login') }}</th>
                                    <td>
                                        @if($driver->last_login)
                                            {{ $driver->last_login->format('Y-m-d H:i:s') }}
                                            <br><small class="text-muted">({{ $driver->last_login->diffForHumans() }})</small>
                                        @else
                                            <span class="badge badge-warning">{{ __('messages.Never_Logged_In') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Created_At') }}</th>
                                    <td>{{ $driver->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Updated_At') }}</th>
                                    <td>{{ $driver->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Documents -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Documents') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    {{ __('messages.Driving_License_Front') }}
                                </div>
                                <div class="card-body text-center">
                                    @if($driver->driving_license_front)
                                    <img src="{{ asset('assets/admin/uploads/' . $driver->driving_license_front) }}" alt="Driving License Front" class="img-fluid rounded" style="max-height: 200px;">
                                    <a href="{{ asset('assets/admin/uploads/' . $driver->driving_license_front) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-eye"></i> {{ __('messages.View_Full_Size') }}
                                    </a>
                                    @else
                                    <div class="bg-light rounded py-5">
                                        <i class="fas fa-id-card fa-3x text-gray-300"></i>
                                        <p class="mt-2 text-gray-500">{{ __('messages.Not_Available') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    {{ __('messages.Driving_License_Back') }}
                                </div>
                                <div class="card-body text-center">
                                    @if($driver->driving_license_back)
                                    <img src="{{ asset('assets/admin/uploads/' . $driver->driving_license_back) }}" alt="Driving License Back" class="img-fluid rounded" style="max-height: 200px;">
                                    <a href="{{ asset('assets/admin/uploads/' . $driver->driving_license_back) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-eye"></i> {{ __('messages.View_Full_Size') }}
                                    </a>
                                    @else
                                    <div class="bg-light rounded py-5">
                                        <i class="fas fa-id-card fa-3x text-gray-300"></i>
                                        <p class="mt-2 text-gray-500">{{ __('messages.Not_Available') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    {{ __('messages.Car_License_Front') }}
                                </div>
                                <div class="card-body text-center">
                                    @if($driver->car_license_front)
                                    <img src="{{ asset('assets/admin/uploads/' . $driver->car_license_front) }}" alt="Car License Front" class="img-fluid rounded" style="max-height: 200px;">
                                    <a href="{{ asset('assets/admin/uploads/' . $driver->car_license_front) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-eye"></i> {{ __('messages.View_Full_Size') }}
                                    </a>
                                    @else
                                    <div class="bg-light rounded py-5">
                                        <i class="fas fa-file-alt fa-3x text-gray-300"></i>
                                        <p class="mt-2 text-gray-500">{{ __('messages.Not_Available') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    {{ __('messages.Car_License_Back') }}
                                </div>
                                <div class="card-body text-center">
                                    @if($driver->car_license_back)
                                    <img src="{{ asset('assets/admin/uploads/' . $driver->car_license_back) }}" alt="Car License Back" class="img-fluid rounded" style="max-height: 200px;">
                                    <a href="{{ asset('assets/admin/uploads/' . $driver->car_license_back) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-eye"></i> {{ __('messages.View_Full_Size') }}
                                    </a>
                                    @else
                                    <div class="bg-light rounded py-5">
                                        <i class="fas fa-file-alt fa-3x text-gray-300"></i>
                                        <p class="mt-2 text-gray-500">{{ __('messages.Not_Available') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                      
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    {{ __('messages.No_criminal_record') }}
                                </div>
                                <div class="card-body text-center">
                                    @if($driver->no_criminal_record)
                                    <img src="{{ asset('assets/admin/uploads/' . $driver->no_criminal_record) }}" alt="No Criminal Record" class="img-fluid rounded" style="max-height: 200px;">
                                    <a href="{{ asset('assets/admin/uploads/' . $driver->no_criminal_record) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-eye"></i> {{ __('messages.View_Full_Size') }}
                                    </a>
                                    @else
                                    <div class="bg-light rounded py-5">
                                        <i class="fas fa-file-alt fa-3x text-gray-300"></i>
                                        <p class="mt-2 text-gray-500">{{ __('messages.Not_Available') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-shopping-bag"></i> {{ __('messages.Orders') }} ({{ $driver->orders_count }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.Order_Number') }}</th>
                            <th>{{ __('messages.User') }}</th>
                            <th>{{ __('messages.Service') }}</th>
                            <th>{{ __('messages.Total_Price_After_Discount') }}</th>
                            <th>{{ __('messages.Driver_Earning') }}</th>
                            <th>{{ __('messages.Payment_Method') }}</th>
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
                            <td>
                                @if($order->user)
                                    <a href="{{ route('users.show', $order->user_id) }}" class="text-primary">
                                        {{ $order->user->name }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ __('messages.N/A') }}</span>
                                @endif
                            </td>
                            <td>{{ $order->service->name ?? __('messages.N/A') }}</td>
                            <td>
                                <strong>{{ number_format($order->total_price_after_discount, 2) }}</strong>
                                @if($order->discount_value > 0)
                                    <br><small class="text-success">
                                        <i class="fas fa-tag"></i> -{{ number_format($order->discount_value, 2) }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">{{ number_format($order->net_price_for_driver, 2) }}</strong>
                                <br><small class="text-muted">
                                    {{ __('messages.Commission') }}: {{ number_format($order->commision_of_admin, 2) }}
                                </small>
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
                {{ $orders->appends(['wallet_page' => request('wallet_page')])->links() }}
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
                                {{ __('messages.No_Transactions_Found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($walletTransactions->hasPages())
            <div class="mt-3">
                {{ $walletTransactions->appends(['orders_page' => request('orders_page')])->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection