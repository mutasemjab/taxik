@extends('layouts.admin')

@section('title')
{{ __('messages.order_report') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-3 text-gray-800">{{ __('messages.order_report') }}</h1>
            <form method="GET" action="{{ route('order_report') }}">
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
                        <label for="representative_id">{{ __('messages.representatives') }}</label>
                        <select id="representative_id" name="representative_id" class="form-control">
                            <option value="">{{ __('messages.select_representative') }}</option>
                            @foreach($representatives as $representative)
                                <option value="{{ $representative->id }}" {{ request('representative_id') == $representative->id ? 'selected' : '' }}>{{ $representative->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="user_type">{{ __('messages.user_type') }}</label>
                        <select id="user_type" name="user_type" class="form-control">
                            <option value="">{{ __('messages.select_user_type') }}</option>
                            <option value="1" {{ request('user_type') == 1 ? 'selected' : '' }}>{{ __('messages.User') }}</option>
                            <option value="2" {{ request('user_type') == 2 ? 'selected' : '' }}>{{ __('messages.WholeSale') }}</option>
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="order_status">{{ __('messages.order_status') }}</label>
                        <select id="order_status" name="order_status" class="form-control">
                            <option value="">{{ __('messages.select_status') }}</option>
                            <option value="1" {{ request('order_status') == 1 ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                            <option value="2" {{ request('order_status') == 2 ? 'selected' : '' }}>{{ __('messages.Accepted') }}</option>
                            <option value="3" {{ request('order_status') == 3 ? 'selected' : '' }}>{{ __('messages.OnTheWay') }}</option>
                            <option value="4" {{ request('order_status') == 4 ? 'selected' : '' }}>{{ __('messages.Delivered') }}</option>
                            <option value="5" {{ request('order_status') == 5 ? 'selected' : '' }}>{{ __('messages.Canceled') }}</option>
                            <option value="6" {{ request('order_status') == 6 ? 'selected' : '' }}>{{ __('messages.Refund') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="to_date">{{ __('messages.to_date') }}</label>
                        <input type="date" id="to_date" name="to_date" class="form-control" value="{{ request('to_date', date('Y-m-d')) }}">
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
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.order_report') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.order_id') }}</th>
                                    <th>{{ __('messages.total_prices') }}</th>
                                    <th>{{ __('messages.order_status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData as $data)
                                <tr>
                                    <td>{{ $data['order_id'] }}</td>
                                    <td>{{ $data['total_prices'] }}</td>
                                    <td>{{ $data['order_status'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
