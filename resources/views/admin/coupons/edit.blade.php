@extends('layouts.admin')

@section('title', __('messages.Edit_Coupon'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Edit_Coupon') }}</h1>
        <a href="{{ route('coupons.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Coupon_Details') }}</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('coupons.update', $coupon->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="code">{{ __('messages.Coupon_Code') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $coupon->code) }}" required>
                            <small class="form-text text-muted">{{ __('messages.Coupon_Code_Info') }}</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">{{ __('messages.Title') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $coupon->title) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="coupon_type">{{ __('messages.Coupon_Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="coupon_type" name="coupon_type" required>
                                <option value="1" {{ old('coupon_type', $coupon->coupon_type) == 1 ? 'selected' : '' }}>{{ __('messages.All_Rides') }}</option>
                                <option value="2" {{ old('coupon_type', $coupon->coupon_type) == 2 ? 'selected' : '' }}>{{ __('messages.First_Ride') }}</option>
                                <option value="3" {{ old('coupon_type', $coupon->coupon_type) == 3 ? 'selected' : '' }}>{{ __('messages.Specific_Service') }}</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="service_group" style="display: {{ $coupon->coupon_type == 3 ? 'block' : 'none' }};">
                            <label for="service_id">{{ __('messages.Service') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="service_id" name="service_id" {{ $coupon->coupon_type == 3 ? 'required' : '' }}>
                                <option value="">{{ __('messages.Select_Service') }}</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id', $coupon->service_id) == $service->id ? 'selected' : '' }}>
                                    {{ $service->name_en }} ({{ $service->name_ar }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="activate">{{ __('messages.Status') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="activate" name="activate" required>
                                <option value="1" {{ old('activate', $coupon->activate) == 1 ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                                <option value="2" {{ old('activate', $coupon->activate) == 2 ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Discount Information -->

                        <div class="form-group">
                            <label for="discount">{{ __('messages.number_of_used') }}</label>
                            <input type="number" class="form-control" id="number_of_used" name="number_of_used" value="{{ old('number_of_used', $coupon->number_of_used) }}">
                        </div>

                        <div class="form-group">
                            <label for="discount">{{ __('messages.Discount_Value') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="{{ old('discount', $coupon->discount) }}" required min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="discount_type">{{ __('messages.Discount_Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="discount_type" name="discount_type" required>
                                <option value="1" {{ old('discount_type', $coupon->discount_type) == 1 ? 'selected' : '' }}>{{ __('messages.Fixed_Amount') }}</option>
                                <option value="2" {{ old('discount_type', $coupon->discount_type) == 2 ? 'selected' : '' }}>{{ __('messages.Percentage') }}</option>
                            </select>
                        </div>
                   

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">{{ __('messages.Start_Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $coupon->start_date->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">{{ __('messages.End_Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', $coupon->end_date->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('messages.Update') }}
                    </button>
                    <a href="{{ route('coupons.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Toggle service select based on coupon type
        $('#coupon_type').on('change', function() {
            if ($(this).val() == '3') {
                $('#service_group').show();
                $('#service_id').prop('required', true);
            } else {
                $('#service_group').hide();
                $('#service_id').prop('required', false);
            }
        });
        
        // Trigger change on page load for initial state
        $('#coupon_type').trigger('change');
        
        // Date validation
        $('#end_date').on('change', function() {
            var startDate = $('#start_date').val();
            var endDate = $(this).val();
            
            if (startDate && endDate && startDate > endDate) {
                alert("{{ __('messages.End_Date_Error') }}");
                $(this).val('');
            }
        });
        
        $('#start_date').on('change', function() {
            var startDate = $(this).val();
            var endDate = $('#end_date').val();
            
            if (startDate && endDate && startDate > endDate) {
                $('#end_date').val('');
            }
        });
    });
</script>
@endsection