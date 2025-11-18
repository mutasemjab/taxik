@extends('layouts.admin')

@section('title', __('messages.Order_Status_History_Report'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line"></i> {{ __('messages.Order_Status_History_Report') }}
        </h1>
        <a href="{{ route('reports.order-status-export', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> {{ __('messages.Export_CSV') }}
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> {{ __('messages.Filters') }}
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.order-status-history') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">{{ __('messages.Date_From') }}</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">{{ __('messages.Date_To') }}</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">{{ __('messages.Status') }}</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">{{ __('messages.All_Statuses') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                    {{ __('messages.Pending') }}
                                </option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>
                                    {{ __('messages.Accepted') }}
                                </option>
                                <option value="on_the_way" {{ request('status') == 'on_the_way' ? 'selected' : '' }}>
                                    {{ __('messages.On_Way') }}
                                </option>
                                <option value="started" {{ request('status') == 'started' ? 'selected' : '' }}>
                                    {{ __('messages.Started') }}
                                </option>
                                <option value="arrived" {{ request('status') == 'arrived' ? 'selected' : '' }}>
                                    {{ __('messages.Arrived') }}
                                </option>
                                <option value="waiting_payment" {{ request('status') == 'waiting_payment' ? 'selected' : '' }}>
                                    {{ __('messages.Waiting_Payment') }}
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    {{ __('messages.Completed') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="driver_id">{{ __('messages.Driver') }}</label>
                            <select class="form-control" id="driver_id" name="driver_id">
                                <option value="">{{ __('messages.All_Drivers') }}</option>
                                @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="user_id">{{ __('messages.User') }}</label>
                            <select class="form-control" id="user_id" name="user_id">
                                <option value="">{{ __('messages.All_Users') }}</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
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
                                @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name_en }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> {{ __('messages.Apply_Filters') }}
                                </button>
                                <a href="{{ route('reports.order-status-history') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> {{ __('messages.Reset') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ __('messages.Orders_List') }} ({{ $orders->total() }} {{ __('messages.Orders') }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.Order_ID') }}</th>
                            <th>{{ __('messages.User') }}</th>
                            <th>{{ __('messages.Driver') }}</th>
                            <th>{{ __('messages.Service') }}</th>
                            <th>{{ __('messages.Current_Status') }}</th>
                            <th>{{ __('messages.Created_At') }}</th>
                            <th>{{ __('messages.Total_Duration') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        @php
                            $totalDuration = app('App\Http\Controllers\Admin\OrderStatusReportController')->calculateOrderTotalDuration($order->id);
                            $durationFormatted = app('App\Http\Controllers\Admin\OrderStatusReportController')->formatDuration($totalDuration);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('reports.order-status-detail', $order->id) }}" class="font-weight-bold">
                                    #{{ $order->id }}
                                </a>
                            </td>
                            <td>
                                @if($order->user)
                                    {{ $order->user->name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($order->driver)
                                    {{ $order->driver->name }}
                                @else
                                    <span class="text-muted">{{ __('messages.Not_Assigned') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($order->service)
                                    {{ $order->service->name_en }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $order->getStatusClass() }} px-3 py-2">
                                    {{ $order->getStatusText() }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="badge badge-info px-3 py-2">
                                    {{ $durationFormatted }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('reports.order-status-detail', $order->id) }}" 
                                   class="btn btn-sm btn-primary" title="{{ __('messages.View_Details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('orders.show', $order->id) }}" 
                                   class="btn btn-sm btn-info" title="{{ __('messages.View_Order') }}">
                                    <i class="fas fa-receipt"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>{{ __('messages.No_Orders_Found') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection