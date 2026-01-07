@extends('layouts.admin')

@section('title', __('messages.Drivers_Financial_Reports'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line"></i> {{ __('messages.Drivers_Financial_Reports') }}
        </h1>
        <div>
            <a href="{{ route('financial-reports.pos-report') }}" class="btn btn-info">
                <i class="fas fa-store"></i> {{ __('messages.POS_Reports') }}
            </a>
            <a href="{{ route('financial-reports.overall-summary') }}" class="btn btn-success">
                <i class="fas fa-chart-pie"></i> {{ __('messages.Overall_Summary') }}
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-filter"></i> {{ __('messages.Select_Report_Period') }}
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('financial-reports.index') }}" method="GET" id="reportForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">
                                <i class="fas fa-calendar-alt"></i> {{ __('messages.Start_Date') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date" class="form-control" 
                                   value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">
                                <i class="fas fa-calendar-alt"></i> {{ __('messages.End_Date') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="end_date" id="end_date" class="form-control" 
                                   value="{{ request('end_date', now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="driver_id">
                                <i class="fas fa-user"></i> {{ __('messages.Driver') }}
                            </label>
                            <select name="driver_id" id="driver_id" class="form-control select2">
                                <option value="">{{ __('messages.All_Drivers') }}</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" 
                                            {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->name }} - {{ $driver->phone }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> {{ __('messages.Generate_Report') }}
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Date Filters -->
                <div class="row">
                    <div class="col-12">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary quick-date" data-period="today">
                                {{ __('messages.Today') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary quick-date" data-period="yesterday">
                                {{ __('messages.Yesterday') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary quick-date" data-period="this_week">
                                {{ __('messages.This_Week') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary quick-date" data-period="last_week">
                                {{ __('messages.Last_Week') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary quick-date" data-period="this_month">
                                {{ __('messages.This_Month') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary quick-date" data-period="last_month">
                                {{ __('messages.Last_Month') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($report))
    <!-- Summary Cards -->
    <div class="row">
        <!-- Total Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Total_Revenue') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_revenue'], 2) }} {{ __('messages.JD') }}
                            </div>
                            <small class="text-muted">{{ __('messages.Your_Total_Income') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Registration_Revenue') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_registration_revenue'], 2) }} {{ __('messages.JD') }}
                            </div>
                            <small class="text-muted">{{ __('messages.From_Driver_Registration') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('messages.Cards_Revenue') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_cards_revenue'], 2) }} {{ __('messages.JD') }}
                            </div>
                            <small class="text-muted">{{ __('messages.From_Recharge_Cards') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Withdrawals -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('messages.Total_Withdrawals') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_withdrawals'], 2) }} {{ __('messages.JD') }}
                            </div>
                            <small class="text-muted">{{ __('messages.Drivers_Withdrawals') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Summary Cards -->
    <div class="row">
        <!-- Total Drivers -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                {{ __('messages.Total_Drivers') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $report['summary']['total_drivers'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- POS Commission -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                {{ __('messages.POS_Commission') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_pos_commission'], 2) }} {{ __('messages.JD') }}
                            </div>
                            <small class="text-muted">{{ __('messages.Paid_To_POS') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Added to Wallets -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                {{ __('messages.Added_To_Wallets') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_added_to_wallets'], 2) }} {{ __('messages.JD') }}
                            </div>
                            <small class="text-muted">{{ __('messages.Total_Recharged') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Report Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> {{ __('messages.Detailed_Report') }}
            </h6>
            <div>
                <button onclick="printReport()" class="btn btn-sm btn-secondary">
                    <i class="fas fa-print"></i> {{ __('messages.Print') }}
                </button>
                <a href="{{ route('financial-reports.export', request()->query()) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> {{ __('messages.Export_Excel') }}
                </a>
                <a href="{{ route('financial-reports.pdf', request()->query()) }}" class="btn btn-sm btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> {{ __('messages.Export_PDF') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('messages.Driver_Name') }}</th>
                            <th>{{ __('messages.Phone') }}</th>
                            <th>{{ __('messages.Current_Balance') }}</th>
                            <th>{{ __('messages.Registration_Paid') }}</th>
                            <th>{{ __('messages.Registration_Kept') }}</th>
                            <th>{{ __('messages.Cards_Count') }}</th>
                            <th>{{ __('messages.Cards_Net') }}</th>
                            <th>{{ __('messages.Withdrawals') }}</th>
                            <th>{{ __('messages.Total_Revenue') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['drivers'] as $index => $driverReport)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $driverReport['driver_name'] }}</strong>
                            </td>
                            <td>{{ $driverReport['driver_phone'] }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ number_format($driverReport['current_balance'], 2) }} {{ __('messages.JD') }}
                                </span>
                            </td>
                            <td>{{ number_format($driverReport['registration']['total_paid'], 2) }}</td>
                            <td>
                                <span class="text-success font-weight-bold">
                                    {{ number_format($driverReport['registration']['amount_kept'], 2) }}
                                </span>
                            </td>
                            <td>{{ $driverReport['cards']['total_cards_used'] }}</td>
                            <td>
                                <span class="text-success font-weight-bold">
                                    {{ number_format($driverReport['cards']['total_net_from_cards'], 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-danger">
                                    {{ number_format($driverReport['wallet_transactions']['total_withdrawals'], 2) }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-primary">
                                    {{ number_format($driverReport['total_revenue_from_driver'], 2) }} {{ __('messages.JD') }}
                                </strong>
                            </td>
                            <td>
                                <a href="{{ route('financial-reports.driver-details', $driverReport['driver_id']) }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" 
                                   class="btn btn-sm btn-info" title="{{ __('messages.View_Details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                {{ __('messages.No_Data_Available') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($report['drivers']) > 0)
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="4" class="text-right">{{ __('messages.Total') }}:</td>
                            <td>{{ number_format($report['summary']['total_registration_revenue'], 2) }}</td>
                            <td>{{ number_format($report['summary']['total_registration_revenue'], 2) }}</td>
                            <td>-</td>
                            <td>{{ number_format($report['summary']['total_cards_revenue'], 2) }}</td>
                            <td>{{ number_format($report['summary']['total_withdrawals'], 2) }}</td>
                            <td class="text-primary">
                                {{ number_format($report['summary']['total_revenue'], 2) }} {{ __('messages.JD') }}
                            </td>
                            <td>-</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="card shadow mb-4">
        <div class="card-body text-center py-5">
            <i class="fas fa-chart-bar fa-5x text-gray-300 mb-4"></i>
            <h5 class="text-gray-600">{{ __('messages.Select_Date_Range_Generate_Report') }}</h5>
            <p class="text-muted">{{ __('messages.Choose_dates_above_view_financial_report') }}</p>
        </div>
    </div>
    @endif
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Initialize DataTable
    $('#dataTable').DataTable({
        "language": {
            "url": "{{ app()->getLocale() == 'ar' ? '//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json' : '' }}"
        },
        "pageLength": 25,
        "order": [[9, 'desc']] // Sort by total revenue
    });
    
    // Quick Date Filters
    $('.quick-date').click(function() {
        const period = $(this).data('period');
        const today = new Date();
        let startDate, endDate;
        
        switch(period) {
            case 'today':
                startDate = endDate = today;
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                startDate = endDate = yesterday;
                break;
            case 'this_week':
                const weekStart = new Date(today);
                weekStart.setDate(today.getDate() - today.getDay());
                startDate = weekStart;
                endDate = today;
                break;
            case 'last_week':
                const lastWeekEnd = new Date(today);
                lastWeekEnd.setDate(today.getDate() - today.getDay() - 1);
                const lastWeekStart = new Date(lastWeekEnd);
                lastWeekStart.setDate(lastWeekEnd.getDate() - 6);
                startDate = lastWeekStart;
                endDate = lastWeekEnd;
                break;
            case 'this_month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = today;
                break;
            case 'last_month':
                const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                startDate = lastMonth;
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
        }
        
        $('#start_date').val(formatDate(startDate));
        $('#end_date').val(formatDate(endDate));
        $('#reportForm').submit();
    });
    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
});

function printReport() {
    window.print();
}
</script>

<style>
@media print {
    .btn, .sidebar, .topbar, .card-header .btn-group {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endsection