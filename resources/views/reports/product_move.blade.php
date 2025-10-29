@extends('layouts.admin')

@section('title')
{{ __('messages.product_move') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-3 text-gray-800">{{ __('messages.product_move') }}</h1>
            <form method="GET" action="{{ route('product_move') }}">
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
                        <label for="product_id">{{ __('messages.products') }}</label>
                        <select id="product_id" name="product_id" class="form-control">
                            <option value="">{{ __('messages.select_product') }}</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name_ar }}</option>
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
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.product_move') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <h3>{{ __('messages.noteVouchers') }}</h3>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.number') }}</th>
                                    <th>{{ __('messages.Date') }}</th>
                                    <th>{{ __('messages.Note') }}</th>
                                    <th>{{ __('messages.From_Warehouse') }}</th>
                                    <th>{{ __('messages.Type') }}</th>
                                    <th>{{ __('messages.Quantity') }}</th>
                                    <th>{{ __('messages.purchasing_Price') }}</th>
                                    <th>{{ __('messages.Note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['noteVouchers'] as $voucher)
                                <tr>
                                    <td>{{ $voucher->number }}</td>
                                    <td>{{ $voucher->date_note_voucher }}</td>
                                    <td>{{ $voucher->note }}</td>
                                    <td>{{ $voucher->fromWarehouse->name }}</td>
                                    <td>{{ $voucher->note_voucher_type_id==1 ? 'ادخال':"اخراج" }}</td>
                                    <td>
                                        @if(isset($reportData['voucherProducts'][$loop->index]))
                                            {{ $reportData['voucherProducts'][$loop->index]->quantity }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($reportData['voucherProducts'][$loop->index]))
                                            {{ $reportData['voucherProducts'][$loop->index]->purchasing_price }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($reportData['voucherProducts'][$loop->index]))
                                            {{ $reportData['voucherProducts'][$loop->index]->note }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>


                        <h3>{{ __('messages.Orders') }}</h3>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.number') }}</th>
                                    <th>{{ __('messages.User') }}</th>
                                    <th>{{ __('messages.Date') }}</th>
                                    <th>{{ __('messages.Status') }}</th>
                                    <th>{{ __('messages.Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['orders'] as $order)
                                <tr>
                                    <td>{{ $order->number }}</td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>{{ $order->date }}</td>
                                    <td>{{ $order->order_status }}</td>
                                    <td>{{ $order->total_prices }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('messages.Quantity') }}</th>
                                                    <th>{{ __('messages.Unit Price') }}</th>
                                                    <th>{{ __('messages.Total Price After Tax') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($order->products as $orderProduct)
                                                <tr>
                                                    <td>{{ $orderProduct->quantity }}</td>
                                                    <td>{{ $orderProduct->unit_price }}</td>
                                                    <td>{{ $orderProduct->total_price_after_tax }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <h3>{{ __('messages.Quantity') }}</h3>
                        <p>{{ __('messages.Available Quantity for User') }}: {{ $reportData['availableQuantityForUser'] }}</p>
                        <p>{{ __('messages.Available Quantity for WholeSale') }}: {{ $reportData['availableQuantityForWholeSale'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
