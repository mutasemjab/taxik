@extends('layouts.admin')

@section('title', __('messages.Driver_Acceptance_Report'))

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-check mr-2 text-primary"></i>
            {{ __('messages.Driver_Acceptance_Report') }}
        </h1>
    </div>

    {{-- Filter Form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Filter') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.driver-acceptance') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date">{{ __('messages.Date') }}</label>
                            <input type="date" class="form-control" id="date" name="date"
                                   value="{{ request('date', $date->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="driver_id">{{ __('messages.Driver') }}</label>
                            <select class="form-control" id="driver_id" name="driver_id">
                                <option value="">{{ __('messages.All_Drivers') }}</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->name }} ({{ $driver->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> {{ __('messages.Filter') }}
                            </button>
                            <a href="{{ route('reports.driver-acceptance') }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> {{ __('messages.Reset') }}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Card --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Total_Active_Drivers') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reportData->count() }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Total_Accepted_Orders') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reportData->sum('orders_count') }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('messages.Date') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $date->format('Y-m-d') }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ __('messages.Drivers_Who_Accepted_Orders') }}
                <span class="text-muted ml-2" style="font-size:0.85em;">{{ $date->format('Y-m-d') }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($reportData->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>{{ __('messages.No_Data_For_This_Date') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('messages.Driver') }}</th>
                                <th>{{ __('messages.Phone') }}</th>
                                <th class="text-center">{{ __('messages.Accepted_Orders') }}</th>
                                <th class="text-center">{{ __('messages.Completed') }}</th>
                                <th class="text-center">{{ __('messages.Cancelled') }}</th>
                                <th>{{ __('messages.First_Acceptance') }}</th>
                                <th>{{ __('messages.Last_Acceptance') }}</th>
                                <th>{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <a href="{{ route('drivers.show', $row->driver_id) }}" class="text-decoration-none">
                                        <strong>{{ $row->driver_name }}</strong>
                                    </a>
                                </td>
                                <td>{{ $row->driver_phone }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary badge-pill" style="font-size:1em;">
                                        {{ $row->orders_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ $row->completed_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $row->cancelled_count > 0 ? 'danger' : 'secondary' }}">
                                        {{ $row->cancelled_count }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($row->first_acceptance)->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($row->last_acceptance)->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('drivers.show', $row->driver_id) }}"
                                       class="btn btn-info btn-sm" title="{{ __('messages.View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('orders.index') }}?driver_id={{ $row->driver_id }}&date_from={{ $date->format('Y-m-d') }}&date_to={{ $date->format('Y-m-d') }}"
                                       class="btn btn-primary btn-sm" title="{{ __('messages.View_Orders') }}">
                                        <i class="fas fa-list"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
