@extends('layouts.admin')

@section('title', __('messages.Order_Status_Details') . ' #' . $order->id)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-history"></i> {{ __('messages.Order_Status_Details') }} #{{ $order->id }}
        </h1>
        <div>
            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info">
                <i class="fas fa-receipt"></i> {{ __('messages.View_Order') }}
            </a>
            <a href="{{ route('reports.order-status-history') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back') }}
            </a>
        </div>
    </div>

    <!-- Order Summary Card -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.User') }}
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ $order->user ? $order->user->name : 'N/A' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Driver') }}
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ $order->driver ? $order->driver->name : __('messages.Not_Assigned') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('messages.Service') }}
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ $order->service ? $order->service->name_en : 'N/A' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('messages.Total_Duration') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalDurationFormatted }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Timeline Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-stream"></i> {{ __('messages.Status_Timeline') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">{{ __('messages.Status') }}</th>
                            <th width="18%">{{ __('messages.Started_At') }}</th>
                            <th width="18%">{{ __('messages.Ended_At') }}</th>
                            <th width="12%">{{ __('messages.Duration') }}</th>
                            <th width="15%">{{ __('messages.Next_Status') }}</th>
                            <th width="17%">{{ __('messages.Changed_By') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historyWithDurations as $index => $history)
                        <tr class="{{ isset($history['is_current']) && $history['is_current'] ? 'table-active' : '' }}">
                            <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                            <td>
                                @if($history['status'])
                                    <span class="badge badge-secondary px-3 py-2">
                                        {{ __('' . Str::studly($history['status'])) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($history['started_at'])
                                    <i class="far fa-clock text-muted"></i>
                                    {{ \Carbon\Carbon::parse($history['started_at'])->format('Y-m-d H:i:s') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($history['ended_at'])
                                    <i class="far fa-clock text-muted"></i>
                                    {{ \Carbon\Carbon::parse($history['ended_at'])->format('Y-m-d H:i:s') }}
                                @else
                                    <span class="badge badge-warning">{{ __('messages.In_Progress') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($history['duration_formatted'])
                                    <span class="badge badge-info px-3 py-2">
                                        <i class="fas fa-hourglass-half"></i> {{ $history['duration_formatted'] }}
                                    </span>
                                    <br>
                                    <small class="text-muted">({{ $history['duration_minutes'] }} min)</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($history['next_status'])
                                    <span class="badge badge-success px-3 py-2">
                                        {{ __('messages.' . Str::studly($history['next_status'])) }}
                                    </span>
                                @else
                                    <span class="badge badge-warning">{{ __('messages.Current') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($history['changed_by'])
                                    @if($history['changed_by_type'] == 'driver')
                                        <i class="fas fa-car text-success"></i>
                                        {{ __('messages.Driver') }}
                                    @else
                                        <i class="fas fa-user text-primary"></i>
                                        {{ __('messages.User') }}
                                    @endif
                                    <br>
                                    <small class="text-muted">ID: {{ $history['changed_by'] }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ __('messages.No_Status_History_Found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <td colspan="4" class="text-right font-weight-bold">
                                {{ __('messages.Total_Order_Duration') }}:
                            </td>
                            <td colspan="3">
                                <span class="badge badge-primary px-4 py-2" style="font-size: 1.1rem;">
                                    <i class="fas fa-clock"></i> {{ $totalDurationFormatted }}
                                </span>
                                <br>
                                <small class="text-muted">({{ $totalDuration }} minutes)</small>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Visual Timeline -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-project-diagram"></i> {{ __('messages.Visual_Timeline') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="timeline">
                @foreach($historyWithDurations as $index => $history)
                    @if($history['status'])
                    <div class="timeline-item {{ isset($history['is_current']) && $history['is_current'] ? 'active' : '' }}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6 class="font-weight-bold">
                                {{ __('messages.' . Str::studly($history['status'])) }}
                                @if(isset($history['is_current']) && $history['is_current'])
                                    <span class="badge badge-warning ml-2">{{ __('messages.Current') }}</span>
                                @endif
                            </h6>
                            <p class="text-muted mb-1">
                                <i class="far fa-clock"></i>
                                {{ \Carbon\Carbon::parse($history['started_at'])->format('Y-m-d H:i:s') }}
                                @if($history['ended_at'])
                                    → {{ \Carbon\Carbon::parse($history['ended_at'])->format('Y-m-d H:i:s') }}
                                @endif
                            </p>
                            @if($history['duration_formatted'])
                            <p class="mb-0">
                                <span class="badge badge-info">
                                    <i class="fas fa-hourglass-half"></i> {{ $history['duration_formatted'] }}
                                </span>
                            </p>
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    padding-bottom: 30px;
    border-left: 2px solid #e3e6f0;
}

.timeline-item:last-child {
    border-left: 2px solid transparent;
}

.timeline-item.active {
    border-left: 2px solid #ffc107;
}

.timeline-marker {
    position: absolute;
    left: -8px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background-color: #4e73df;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #4e73df;
}

.timeline-item.active .timeline-marker {
    background-color: #ffc107;
    box-shadow: 0 0 0 2px #ffc107;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 2px #ffc107;
    }
    50% {
        box-shadow: 0 0 0 6px rgba(255, 193, 7, 0.3);
    }
    100% {
        box-shadow: 0 0 0 2px #ffc107;
    }
}

.timeline-content {
    background-color: #f8f9fc;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.timeline-item.active .timeline-content {
    background-color: #fff3cd;
    border: 1px solid #ffc107;
}
</style>
@endsection