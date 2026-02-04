@extends('layouts.admin')

@section('title', __('messages.Create_Service'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Create_Service') }}</h1>
        <a href="{{ route('services.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Service_Details') }}</h6>
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

            <form action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name_en">{{ __('messages.Name_English') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en') }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="name_ar">{{ __('messages.Name_Arabic') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="photo">{{ __('messages.Photo') }} <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="photo" name="photo" required>
                                <label class="custom-file-label" for="photo">{{ __('messages.Choose_file') }}</label>
                            </div>
                            <div class="mt-3" id="image-preview"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="capacity">{{ __('messages.Capacity') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="{{ old('capacity', 0) }}" required min="0">
                            <small class="form-text text-muted">{{ __('messages.Capacity_Info') }}</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Other Settings -->
                        <div class="form-group">
                            <label for="waiting_time">{{ __('messages.Waiting_Time') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="waiting_time" name="waiting_time" value="{{ old('waiting_time', 0) }}" required min="0">
                            <small class="form-text text-muted">{{ __('messages.Waiting_Time_Info') }}</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="cancellation_fee">{{ __('messages.Cancellation_Fee') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="cancellation_fee" name="cancellation_fee" value="{{ old('cancellation_fee', 0) }}" required min="0">
                        </div>

                        <div class="form-group">
                            <label for="is_electric">{{ __('messages.is_electric') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="is_electric" name="is_electric" required>
                                <option value="1" {{ old('is_electric', 1) == 1 ? 'selected' : '' }}>{{ __('messages.Yes') }}</option>
                                <option value="2" {{ old('is_electric') == 2 ? 'selected' : '' }}>{{ __('messages.No') }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="activate">{{ __('messages.Status') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="activate" name="activate" required>
                                <option value="1" {{ old('activate', 1) == 1 ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                                <option value="2" {{ old('activate') == 2 ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <hr>

                <!-- Morning Pricing -->
                <h5 class="text-primary mb-3"><i class="fas fa-sun"></i> {{ __('messages.Morning_Pricing') }}</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="start_price_morning">{{ __('messages.Start_Price') }} ({{ __('messages.Morning') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="start_price_morning" name="start_price_morning" value="{{ old('start_price_morning', 0) }}" required min="0">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="price_per_km_morning">{{ __('messages.Price_Per_KM') }} ({{ __('messages.Morning') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="price_per_km_morning" name="price_per_km_morning" value="{{ old('price_per_km_morning', 0) }}" required min="0">
                        </div>
                    </div>

                    
                </div>

                <hr>

                <!-- Evening Pricing -->
                <h5 class="text-warning mb-3"><i class="fas fa-moon"></i> {{ __('messages.Evening_Pricing') }}</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="start_price_evening">{{ __('messages.Start_Price') }} ({{ __('messages.Evening') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="start_price_evening" name="start_price_evening" value="{{ old('start_price_evening', 0) }}" required min="0">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="price_per_km_evening">{{ __('messages.Price_Per_KM') }} ({{ __('messages.Evening') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="price_per_km_evening" name="price_per_km_evening" value="{{ old('price_per_km_evening', 0) }}" required min="0">
                        </div>
                    </div>

                </div>
                
                <hr>

                <!-- NEW: Waiting Charges Section -->
                <h5 class="text-info mb-3"><i class="fas fa-clock"></i> {{ __('messages.Waiting_Charges') }}</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="free_waiting_minutes">{{ __('messages.Free_Waiting_Minutes') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="free_waiting_minutes" name="free_waiting_minutes" value="{{ old('free_waiting_minutes', 3) }}" required min="0">
                            <small class="form-text text-muted">{{ __('messages.Free_Waiting_Minutes_Info') }}</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="waiting_charge_per_minute">{{ __('messages.Waiting_Charge_Per_Minute') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="waiting_charge_per_minute" name="waiting_charge_per_minute" value="{{ old('waiting_charge_per_minute', 0) }}" required min="0">
                            <small class="form-text text-muted">{{ __('messages.Waiting_Charge_Per_Minute_Info') }}</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="waiting_charge_per_minute_when_order_active">{{ __('messages.In_Trip_Waiting_Charge') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="waiting_charge_per_minute_when_order_active" name="waiting_charge_per_minute_when_order_active" value="{{ old('waiting_charge_per_minute_when_order_active', 0) }}" required min="0">
                            <small class="form-text text-muted">{{ __('messages.In_Trip_Waiting_Charge_Info') }}</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Commission and Payment Settings -->
                <h5 class="text-success mb-3"><i class="fas fa-cog"></i> {{ __('messages.Commission_and_Payment') }}</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="admin_commision">{{ __('messages.Admin_Commission') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="admin_commision" name="admin_commision" value="{{ old('admin_commision', 0) }}" required min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="type_of_commision">{{ __('messages.Commission_Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="type_of_commision" name="type_of_commision" required>
                                <option value="1" {{ old('type_of_commision', 1) == 1 ? 'selected' : '' }}>{{ __('messages.Fixed_Amount') }}</option>
                                <option value="2" {{ old('type_of_commision') == 2 ? 'selected' : '' }}>{{ __('messages.Percentage') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Payment Methods') }} <span class="text-danger">*</span></label>
                            <div class="checkbox-list">
                                <label class="checkbox">
                                    <input type="checkbox" name="payment_methods[]" value="cash" {{ old('payment_methods') && in_array('cash', old('payment_methods')) ? 'checked' : '' }}>
                                    <span></span>{{ __('Cash') }}
                                </label>
                                <label class="checkbox">
                                    <input type="checkbox" name="payment_methods[]" value="visa" {{ old('payment_methods') && in_array('visa', old('payment_methods')) ? 'checked' : '' }}>
                                    <span></span>{{ __('Visa') }}
                                </label>
                                <label class="checkbox">
                                    <input type="checkbox" name="payment_methods[]" value="wallet" {{ old('payment_methods') && in_array('wallet', old('payment_methods')) ? 'checked' : '' }}>
                                    <span></span>{{ __('Wallet') }}
                                </label>
                                <label class="checkbox">
                                    <input type="checkbox" name="payment_methods[]" value="app_credit" {{ old('payment_methods') && in_array('app_credit', old('payment_methods')) ? 'checked' : '' }}>
                                    <span></span>{{ __('App_credit') }}
                                </label>
                            </div>
                            @error('payment_methods')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('messages.Save') }}
                    </button>
                    <a href="{{ route('services.index') }}" class="btn btn-secondary">
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
    // Show image preview
    $(document).ready(function() {
        // Show filename on file select
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
            
            // Image preview
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').html('<img src="' + e.target.result + '" class="img-fluid img-thumbnail" style="max-height: 200px;">');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
@endsection