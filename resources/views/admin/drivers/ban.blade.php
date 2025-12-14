@extends('layouts.admin')

@section('title', __('messages.Ban_Driver'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-ban text-danger"></i> {{ __('messages.Ban_Driver') }}
        </h1>
        <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_To_Drivers') }}
        </a>
    </div>

    <!-- Driver Info Card -->
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Driver_Information') }}</h6>
                </div>
                <div class="card-body text-center">
                    @if($driver->photo)
                    <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}" alt="{{ $driver->name }}" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                    <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="{{ __('messages.No_Image') }}" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @endif
                    
                    <h5>{{ $driver->name }}</h5>
                    <p class="text-muted mb-1">
                        <i class="fas fa-phone"></i> {{ $driver->country_code }} {{ $driver->phone }}
                    </p>
                    @if($driver->email)
                    <p class="text-muted mb-1">
                        <i class="fas fa-envelope"></i> {{ $driver->email }}
                    </p>
                    @endif
                    
                    <hr>
                    
                    <div class="text-left">
                        <p class="mb-2"><strong>{{ __('messages.Car_Model') }}:</strong> {{ $driver->model ?? __('messages.N/A') }}</p>
                        <p class="mb-2"><strong>{{ __('messages.Plate_Number') }}:</strong> {{ $driver->plate_number ?? __('messages.N/A') }}</p>
                        <p class="mb-2"><strong>{{ __('messages.Balance') }}:</strong> {{ $driver->balance }}</p>
                        <p class="mb-2">
                            <strong>{{ __('messages.Status') }}:</strong> 
                            @if($driver->activate == 1)
                            <span class="badge badge-success">{{ __('messages.Active') }}</span>
                            @elseif($driver->activate == 2)
                            <span class="badge badge-danger">{{ __('messages.Banned') }}</span>
                            @else
                            <span class="badge badge-warning">{{ __('messages.Waiting_Approve') }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ban Form -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-danger">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-ban"></i> {{ __('messages.Ban_This_Driver') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($driver->activate == 2)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>{{ __('messages.Warning') }}:</strong> {{ __('messages.Driver_Already_Banned_Message') }}
                    </div>
                    @endif

                    <form action="{{ route('drivers.ban', $driver->id) }}" method="POST">
                        @csrf
                        
                        <!-- Ban Type -->
                        <div class="form-group">
                            <label for="ban_type">{{ __('messages.Ban_Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('ban_type') is-invalid @enderror" id="ban_type" name="ban_type" required>
                                <option value="">{{ __('messages.Select_Ban_Type') }}</option>
                                <option value="temporary" {{ old('ban_type') == 'temporary' ? 'selected' : '' }}>{{ __('messages.Temporary_Ban') }}</option>
                                <option value="permanent" {{ old('ban_type') == 'permanent' ? 'selected' : '' }}>{{ __('messages.Permanent_Ban') }}</option>
                            </select>
                            @error('ban_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ban Duration (for temporary ban) -->
                        <div id="durationFields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ban_duration">{{ __('messages.Duration') }} <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('ban_duration') is-invalid @enderror" id="ban_duration" name="ban_duration" min="1" value="{{ old('ban_duration') }}" placeholder="{{ __('messages.Enter_Duration') }}">
                                        @error('ban_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ban_duration_unit">{{ __('messages.Unit') }} <span class="text-danger">*</span></label>
                                        <select class="form-control @error('ban_duration_unit') is-invalid @enderror" id="ban_duration_unit" name="ban_duration_unit">
                                            <option value="hours" {{ old('ban_duration_unit') == 'hours' ? 'selected' : '' }}>{{ __('messages.Hours') }}</option>
                                            <option value="days" {{ old('ban_duration_unit') == 'days' ? 'selected' : '' }}>{{ __('messages.Days') }}</option>
                                            <option value="weeks" {{ old('ban_duration_unit') == 'weeks' ? 'selected' : '' }}>{{ __('messages.Weeks') }}</option>
                                            <option value="months" {{ old('ban_duration_unit') == 'months' ? 'selected' : '' }}>{{ __('messages.Months') }}</option>
                                        </select>
                                        @error('ban_duration_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ban Reason -->
                        <div class="form-group">
                            <label for="ban_reason">{{ __('messages.Ban_Reason') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('ban_reason') is-invalid @enderror" id="ban_reason" name="ban_reason" required>
                                <option value="">{{ __('messages.Select_Reason') }}</option>
                                @foreach($banReasons as $key => $reason)
                                <option value="{{ $key }}" {{ old('ban_reason') == $key ? 'selected' : '' }}>{{ __('messages.ban_reason_' . $key) }}</option>
                                @endforeach
                            </select>
                            @error('ban_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Custom Reason (shown when "Other" is selected) -->
                        <div class="form-group" id="customReasonField" style="display: none;">
                            <label for="custom_ban_reason">{{ __('messages.Custom_Reason') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="custom_ban_reason" placeholder="{{ __('messages.Enter_Custom_Reason') }}">
                        </div>

                        <!-- Ban Description -->
                        <div class="form-group">
                            <label for="ban_description">{{ __('messages.Additional_Details') }} ({{ __('messages.Optional') }})</label>
                            <textarea class="form-control @error('ban_description') is-invalid @enderror" id="ban_description" name="ban_description" rows="4" placeholder="{{ __('messages.Provide_Additional_Details') }}">{{ old('ban_description') }}</textarea>
                            @error('ban_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.Ban_Description_Help') }}</small>
                        </div>

                        <!-- Warning -->
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <strong>{{ __('messages.Warning') }}:</strong> 
                            {{ __('messages.Ban_Warning_Message') }}
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-ban"></i> {{ __('messages.Ban_Driver') }}
                            </button>
                            <a href="{{ route('drivers.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Show/hide duration fields based on ban type
    $('#ban_type').change(function() {
        if ($(this).val() === 'temporary') {
            $('#durationFields').slideDown();
            $('#ban_duration').prop('required', true);
            $('#ban_duration_unit').prop('required', true);
        } else {
            $('#durationFields').slideUp();
            $('#ban_duration').prop('required', false);
            $('#ban_duration_unit').prop('required', false);
        }
    });

    // Show/hide custom reason field
    $('#ban_reason').change(function() {
        if ($(this).val() === 'other') {
            $('#customReasonField').slideDown();
            $('#custom_ban_reason').prop('required', true);
            
            // Update the ban_reason value when custom reason is entered
            $('#custom_ban_reason').on('input', function() {
                $('#ban_reason').val($(this).val());
            });
        } else {
            $('#customReasonField').slideUp();
            $('#custom_ban_reason').prop('required', false);
        }
    });

    // Trigger change event on page load if old values exist
    @if(old('ban_type') == 'temporary')
    $('#durationFields').show();
    @endif

    @if(old('ban_reason') == 'other')
    $('#customReasonField').show();
    @endif
});
</script>
@endsection