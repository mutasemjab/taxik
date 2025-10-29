@extends('layouts.admin')

@section('title')
{{ __('messages.tax_report') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-3 text-gray-800">{{ __('messages.tax_report') }}</h1>
            <form method="GET" action="{{ route('tax_report') }}">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3">
                        <label for="shop_id">{{ __('messages.shop') }}</label>
                        <select id="shop_id" name="shop_id" class="form-control" required>
                            <option value="">{{ __('messages.select_shop') }}</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="from_date">{{ __('messages.from_date') }}</label>
                        <input type="date" id="from_date" name="from_date" class="form-control" value="{{ request('from_date', date('Y-m-01')) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="to_date">{{ __('messages.to_date') }}</label>
                        <input type="date" id="to_date" name="to_date" class="form-control" value="{{ request('to_date', date('Y-m-t')) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <button type="submit" class="btn btn-primary">{{ __('messages.Show') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($reportData))
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.tax_report') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.Tax Percentage') }}</th>
                                    <th>{{ __('messages.Sales Orders Total') }}</th>
                                    <th>{{ __('messages.Sales Tax Total') }}</th>
                                    <th>{{ __('messages.Refund Orders Total') }}</th>
                                    <th>{{ __('messages.Refund Tax Total') }}</th>
                                    <th>{{ __('messages.Net Orders Total') }}</th>
                                    <th>{{ __('messages.Net Tax Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData as $data)
                                <tr>
                                    <td>{{ $data['tax'] }}%</td>
                                    <td>{{ number_format($data['sales'], 2) }}</td>
                                    <td>{{ number_format($data['tax_amount_sales'], 2) }}</td>
                                    <td>{{ number_format($data['refund'], 2) }}</td>
                                    <td>{{ number_format($data['tax_amount_refund'], 2) }}</td>
                                    <td>{{ number_format($data['net'], 2) }}</td>
                                    <td>{{ number_format($data['net_tax'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5">{{ __('messages.Total') }}</th>
                                    <th>{{ number_format($netTotal, 2) }}</th>
                                    <th>{{ number_format($netTaxTotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
