@extends('layouts.admin')

@section('title')
{{ __('messages.inventory_report_with_costs') }}
@endsection

@section('content')
<div class="container">
    <h1 class="my-4">Inventory Report</h1>
    <form method="GET" action="{{ route('inventory_report') }}" class="mb-4">
        <div class="form-group">
            <label for="shop_id">Shop:</label>
            <select id="shop_id" name="shop_id" class="form-control" required>
                <option value="">Select Shop</option>
                @foreach($shops as $shop)
                    <option value="{{ $shop->id }}" {{ (old('shop_id') ?? $shopId) == $shop->id ? 'selected' : '' }}>
                        {{ $shop->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="to_date">To Date:</label>
            <input type="date" id="to_date" name="to_date" class="form-control" value="{{ old('to_date', $toDate) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>
    @if(!empty($reportData))
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Product Name</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Weighted Average Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reportData as $data)
                    <tr>
                        <td>{{ $data['product_name'] }}</td>
                        <td>{{ $data['quantity'] }}</td>
                        <td>{{ $data['unit'] }}</td>
                        <td>{{ number_format($data['weighted_average_cost'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No data available for the selected criteria.</p>
    @endif
</div>
@endsection
