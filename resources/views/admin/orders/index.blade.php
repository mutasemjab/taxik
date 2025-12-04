@extends('layouts.admin')

@section('title', __('messages.Orders'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Orders') }}</h1>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_Order') }}
        </a>
    </div>


    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Filter_Orders') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('orders.filter') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="user_id">{{ __('messages.User') }}</label>
                            <select class="form-control" id="user_id" name="user_id">
                                <option value="">{{ __('messages.All_Users') }}</option>
                                @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->phone ?? $user->email }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="driver_id">{{ __('messages.Driver') }}</label>
                            <select class="form-control" id="driver_id" name="driver_id">
                                <option value="">{{ __('messages.All_Drivers') }}</option>
                                @foreach($drivers ?? [] as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }} ({{ $driver->phone ?? $driver->email }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="service_id">{{ __('messages.Service') }}</label>
                            <select class="form-control" id="service_id" name="service_id">
                                <option value="">{{ __('messages.All_Services') }}</option>
                                @foreach($services ?? [] as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name_en ?? $service->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">{{ __('messages.Status') }}</label>
                            <select class="form-control" id="status" name="status">
                                <option value="" {{ request('status') == '' ? 'selected' : '' }}>{{ __('messages.All_Statuses') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                <option value="driver_accepted" {{ request('status') == 'driver_accepted' ? 'selected' : '' }}>{{ __('messages.Driver_Accepted') }}</option>
                                <option value="driver_go_to_user" {{ request('status') == 'driver_go_to_user' ? 'selected' : '' }}>{{ __('messages.Driver_Going_To_User') }}</option>
                                <option value="user_with_driver" {{ request('status') == 'user_with_driver' ? 'selected' : '' }}>{{ __('messages.User_With_Driver') }}</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('messages.Delivered') }}</option>
                                <option value="user_cancel_order" {{ request('status') == 'user_cancel_order' ? 'selected' : '' }}>{{ __('messages.User_Cancelled') }}</option>
                                <option value="driver_cancel_order" {{ request('status') == 'driver_cancel_order' ? 'selected' : '' }}>{{ __('messages.Driver_Cancelled') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_method">{{ __('messages.Payment_Method') }}</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="" {{ request('payment_method') == '' ? 'selected' : '' }}>{{ __('messages.All_Methods') }}</option>
                                <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>{{ __('messages.Cash') }}</option>
                                <option value="visa" {{ request('payment_method') == 'visa' ? 'selected' : '' }}>{{ __('messages.Visa') }}</option>
                                <option value="wallet" {{ request('payment_method') == 'wallet' ? 'selected' : '' }}>{{ __('messages.Wallet') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status_payment">{{ __('messages.Payment_Status') }}</label>
                            <select class="form-control" id="status_payment" name="status_payment">
                                <option value="" {{ request('status_payment') == '' ? 'selected' : '' }}>{{ __('messages.All') }}</option>
                                <option value="pending" {{ request('status_payment') == 'pending' ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                <option value="paid" {{ request('status_payment') == 'paid' ? 'selected' : '' }}>{{ __('messages.Paid') }}</option>
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
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> {{ __('messages.Reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Total_Orders') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orders->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Completed_Orders') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orders->where('status', 'delivered')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                {{ __('messages.Cancelled_Orders') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orders->whereIn('status', ['user_cancel_order', 'driver_cancel_order'])->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('messages.Total_Revenue') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                               JD {{ number_format($orders->where('status', 'delivered')->sum('total_price_after_discount'), 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Orders_List') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Order_Number') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.User') }}</th>
                            <th>{{ __('messages.Driver') }}</th>
                            <th>{{ __('messages.Service') }}</th>
                            <th>{{ __('messages.Route') }}</th>
                            <th>{{ __('messages.Distance') }}</th>
                            <th>{{ __('messages.Price') }}</th>
                            <th>{{ __('messages.Commission') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Payment') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>
                                <span class="font-weight-bold text-primary">{{ $order->number ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($order->user)
                                <a href="{{ route('users.show', $order->user_id) }}" class="text-decoration-none">
                                    <strong>{{ $order->user->name }}</strong><br>
                                    <small class="text-muted">{{ $order->user->phone ?? $order->user->email }}</small>
                                </a>
                                @else
                                <span class="text-muted">{{ __('messages.Not_Available') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($order->driver)
                                <a href="{{ route('drivers.show', $order->driver_id) }}" class="text-decoration-none">
                                    <strong>{{ $order->driver->name }}</strong><br>
                                    <small class="text-muted">{{ $order->driver->phone ?? $order->driver->email }}</small>
                                </a>
                                @else
                                <span class="text-warning">{{ __('messages.Not_Assigned') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($order->service)
                                <a href="{{ route('services.show', $order->service_id) }}" class="text-decoration-none">
                                    {{ $order->service->name_en ?? $order->service->name }}
                                </a>
                                @else
                                <span class="text-muted">{{ __('messages.Not_Available') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="route-info">
                                    <div class="text-success">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <small>{{ Str::limit($order->pick_name, 20) }}</small>
                                    </div>
                                    <div class="text-center text-muted my-1">
                                        <i class="fas fa-arrow-down"></i>
                                    </div>
                                    <div class="text-danger">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <small>{{ Str::limit($order->drop_name, 20) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $order->getDistance() }} km</span>
                            </td>
                            <td>
                                <div class="price-info">
                                    @if($order->discount_value > 0)
                                    <div class="text-muted">
                                        <small><s>JD {{ number_format($order->total_price_before_discount, 2) }}</s></small>
                                    </div>
                                    <div class="text-success font-weight-bold">
                                        JD {{ number_format($order->total_price_after_discount, 2) }}
                                    </div>
                                    <div>
                                        <span class="badge badge-warning">
                                            -JD {{ number_format($order->discount_value, 2) }} ({{ $order->getDiscountPercentage() }}%)
                                        </span>
                                    </div>
                                    @else
                                    <div class="text-success font-weight-bold">
                                        JD {{ number_format($order->total_price_after_discount, 2) }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="commission-info">
                                    <div class="text-primary">
                                        <small>{{ __('messages.Driver') }}: JD {{ number_format($order->net_price_for_driver, 2) }}</small>
                                    </div>
                                    <div class="text-info">
                                        <small>{{ __('messages.Admin') }}: JD {{ number_format($order->commision_of_admin, 2) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $order->getStatusClass() }}">
                                    {{ $order->getStatusText() }}
                                </span>
                                @if($order->reason_for_cancel && $order->isCancelled())
                                <div class="mt-1">
                                    <small class="text-muted" title="{{ $order->reason_for_cancel }}">
                                        <i class="fas fa-info-circle"></i> {{ __('messages.Reason_Available') }}
                                    </small>
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="payment-info">
                                    <div>
                                        <span class="badge badge-primary">{{ $order->getPaymentMethodText() }}</span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge badge-{{ $order->getPaymentStatusClass() }}">
                                            {{ $order->getPaymentStatusText() }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group-vertical">
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info btn-sm mb-1" title="{{ __('messages.View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary btn-sm mb-1" title="{{ __('messages.Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm" onclick="deleteOrder({{ $order->id }})" title="{{ __('messages.Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-form-{{ $order->id }}" action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if(method_exists($orders, 'links'))
            <div class="d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .route-info {
        min-width: 120px;
    }
    
    .price-info {
        min-width: 100px;
    }
    
    .commission-info {
        min-width: 120px;
    }
    
    .payment-info {
        min-width: 100px;
    }
    
    .btn-group-vertical .btn {
        border-radius: 0.25rem !important;
        margin-bottom: 2px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    @media (max-width: 768px) {
        .btn-group-vertical {
            display: flex;
            flex-direction: row;
        }
        
        .btn-group-vertical .btn {
            margin-right: 2px;
            margin-bottom: 0;
        }
    }
</style>
@endsection