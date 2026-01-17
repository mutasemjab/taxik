@extends('layouts.admin')

@section('title', __('messages.Spam_Orders'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-trash-alt"></i> {{ __('messages.Spam_Orders') }}
        </h1>
        <div>
            <a href="{{ route('spam-orders.analytics') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> {{ __('messages.Analytics') }}
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Total_Spam_Orders') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trash fa-2x text-gray-300"></i>
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
                                {{ __('messages.User_Cancelled') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['user_cancelled']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
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
                                {{ __('messages.Driver_Cancelled') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['driver_cancelled']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car-crash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('messages.Auto_Cancelled') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['auto_cancelled']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-robot fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> {{ __('messages.Filters') }}
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('spam-orders.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">{{ __('messages.Order_Number') }}</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="{{ __('messages.Search') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">{{ __('messages.Status') }}</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">{{ __('messages.All') }}</option>
                                <option value="user_cancel_order" {{ request('status') == 'user_cancel_order' ? 'selected' : '' }}>
                                    {{ __('messages.User_Cancelled') }}
                                </option>
                                <option value="driver_cancel_order" {{ request('status') == 'driver_cancel_order' ? 'selected' : '' }}>
                                    {{ __('messages.Driver_Cancelled') }}
                                </option>
                                <option value="cancel_cron_job" {{ request('status') == 'cancel_cron_job' ? 'selected' : '' }}>
                                    {{ __('messages.Auto_Cancelled') }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="from_date">{{ __('messages.Date_From') }}</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" 
                                   value="{{ request('from_date') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="to_date">{{ __('messages.Date_To') }}</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" 
                                   value="{{ request('to_date') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> {{ __('messages.Filter') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Spam Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Spam_Orders_List') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Order_Number') }}</th>
                            <th>{{ __('messages.User') }}</th>
                            <th>{{ __('messages.Service') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Price') }}</th>
                            <th>{{ __('messages.Cancelled_At') }}</th>
                            <th>{{ __('messages.Reason') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spamOrders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>
                                <span class="badge badge-secondary">{{ $order->number }}</span>
                            </td>
                            <td>
                                @if($order->user)
                                    <a href="{{ route('users.show', $order->user_id) }}">
                                        {{ $order->user->name }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
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
                                @if($order->status == 'user_cancel_order')
                                    <span class="badge badge-danger">{{ __('messages.User_Cancelled') }}</span>
                                @elseif($order->status == 'driver_cancel_order')
                                    <span class="badge badge-warning">{{ __('messages.Driver_Cancelled') }}</span>
                                @elseif($order->status == 'cancel_cron_job')
                                    <span class="badge badge-info">{{ __('messages.Auto_Cancelled') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td>{{ number_format($order->total_price_after_discount, 2) }} JD</td>
                            <td>
                                @if($order->cancelled_at)
                                    {{ $order->cancelled_at->format('Y-m-d H:i') }}
                                    <br>
                                    <small class="text-muted">{{ $order->cancelled_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($order->reason_for_cancel)
                                    <small>{{ Str::limit($order->reason_for_cancel, 30) }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('spam-orders.show', $order->id) }}" 
                                   class="btn btn-sm btn-info" title="{{ __('messages.View_Details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('spam-orders.destroy', $order->id) }}" 
                                      method="POST" class="d-inline" 
                                      onsubmit="return confirm('{{ __('messages.Confirm_Delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ __('messages.Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ __('messages.No_Spam_Orders_Found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $spamOrders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection