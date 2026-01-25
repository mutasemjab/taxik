@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.edit_distribution') }}</h3>
                </div>

                <form action="{{ route('wallet-distributions.update', $distribution->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <!-- المبلغ الإجمالي -->
                        <div class="form-group">
                            <label for="total_amount">{{ __('messages.total_amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       name="total_amount" 
                                       id="total_amount" 
                                       class="form-control @error('total_amount') is-invalid @enderror" 
                                       value="{{ old('total_amount', $distribution->total_amount) }}"
                                       step="0.01"
                                       min="0.01"
                                       required
                                       oninput="calculatePerOrder()">
                                <div class="input-group-append">
                                    <span class="input-group-text">JD</span>
                                </div>
                                @error('total_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- عدد الرحلات -->
                        <div class="form-group">
                            <label for="number_of_orders">{{ __('messages.number_of_orders') }} <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="number_of_orders" 
                                   id="number_of_orders" 
                                   class="form-control @error('number_of_orders') is-invalid @enderror" 
                                   value="{{ old('number_of_orders', $distribution->number_of_orders) }}"
                                   min="1"
                                   required
                                   oninput="calculatePerOrder()">
                            @error('number_of_orders')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- المبلغ لكل رحلة -->
                        <div class="form-group">
                            <label>{{ __('messages.amount_per_order') }}</label>
                            <div class="input-group">
                                <input type="text" 
                                       id="amount_per_order_display" 
                                       class="form-control bg-light" 
                                       value="{{ number_format($distribution->amount_per_order, 2) }}"
                                       readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">JD</span>
                                </div>
                            </div>
                        </div>

                        <!-- تفعيل -->
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       name="activate" 
                                       class="custom-control-input" 
                                       id="activate"
                                       {{ old('activate', $distribution->activate) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activate">
                                    {{ __('messages.activate_distribution') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('messages.update') }}
                        </button>
                        <a href="{{ route('wallet-distributions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function calculatePerOrder() {
    const totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;
    const numberOfOrders = parseInt(document.getElementById('number_of_orders').value) || 1;
    const amountPerOrder = totalAmount / numberOfOrders;
    
    document.getElementById('amount_per_order_display').value = amountPerOrder.toFixed(2);
}
</script>
@endpush
@endsection