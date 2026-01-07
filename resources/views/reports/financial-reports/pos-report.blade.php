@extends('layouts.admin')

@section('title', __('messages.POS_Financial_Report'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-store"></i> {{ __('messages.POS_Financial_Report') }}
        </h1>
        <a href="{{ route('financial-reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_Drivers_Report') }}
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-filter"></i> {{ __('messages.Select_Report_Period') }}
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('financial-reports.pos-report') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">{{ __('messages.Start_Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" 
                                   value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">{{ __('messages.End_Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="{{ request('end_date', now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="pos_id">{{ __('messages.POS_Point') }}</label>
                            <select name="pos_id" class="form-control select2">
                                <option value="">{{ __('messages.All_POS') }}</option>
                                @foreach($posPoints as $pos)
                                    <option value="{{ $pos->id }}" {{ request('pos_id') == $pos->id ? 'selected' : '' }}>
                                        {{ $pos->name }} - {{ $pos->phone }}
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
            </form>
        </div>
    </div>

    @if(isset($report))
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Total_POS_Points') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $report['summary']['total_pos'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-store fa-2x text-gray-300"></i>
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
                                {{ __('messages.Total_Cards_Sold') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $report['summary']['total_cards_sold'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
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
                                {{ __('messages.Total_Sales_Value') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_sales_value'], 2) }} {{ __('messages.JD') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                {{ __('messages.POS_Commission') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_commission_paid'], 2) }} {{ __('messages.JD') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-primary shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Net_Revenue_To_Admin') }}
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($report['summary']['total_net_revenue'], 2) }} {{ __('messages.JD') }}
                            </div>
                            <small class="text-muted">{{ __('messages.After_POS_Commission') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-3x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Report Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> {{ __('messages.Detailed_POS_Report') }}
            </h6>
            <div>
                <button onclick="window.print()" class="btn btn-sm btn-secondary">
                    <i class="fas fa-print"></i> {{ __('messages.Print') }}
                </button>
                <a href="{{ route('financial-reports.pos-export', request()->query()) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> {{ __('messages.Export_Excel') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('messages.POS_Name') }}</th>
                            <th>{{ __('messages.Phone') }}</th>
                            <th>{{ __('messages.Cards_Sold') }}</th>
                            <th>{{ __('messages.Total_Sales') }}</th>
                            <th>{{ __('messages.Commission') }}</th>
                            <th>{{ __('messages.Net_To_Admin') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['pos_points'] as $index => $posReport)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $posReport['pos_name'] }}</strong></td>
                            <td>{{ $posReport['pos_phone'] }}</td>
                            <td>{{ $posReport['total_cards_sold'] }}</td>
                            <td>{{ number_format($posReport['total_sales_value'], 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-danger">{{ number_format($posReport['total_commission'], 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-success font-weight-bold">
                                {{ number_format($posReport['net_due_to_admin'], 2) }} {{ __('messages.JD') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ __('messages.No_Data_Available') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($report['pos_points']) > 0)
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="3" class="text-right">{{ __('messages.Total') }}:</td>
                            <td>{{ $report['summary']['total_cards_sold'] }}</td>
                            <td>{{ number_format($report['summary']['total_sales_value'], 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-danger">{{ number_format($report['summary']['total_commission_paid'], 2) }} {{ __('messages.JD') }}</td>
                            <td class="text-success">{{ number_format($report['summary']['total_net_revenue'], 2) }} {{ __('messages.JD') }}</td>
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
            <i class="fas fa-store fa-5x text-gray-300 mb-4"></i>
            <h5 class="text-gray-600">{{ __('messages.Select_Date_Range_Generate_Report') }}</h5>
            <p class="text-muted">{{ __('messages.Choose_dates_above_view_pos_report') }}</p>
        </div>
    </div>
    @endif
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4'
    });
    
    $('#dataTable').DataTable({
        "language": {
            "url": "{{ app()->getLocale() == 'ar' ? '//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json' : '' }}"
        }
    });
});
</script>
@endsection