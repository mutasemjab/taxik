@extends('layouts.admin')

@section('title', __('messages.Create_Driver'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Create_Driver') }}</h1>
        <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Driver_Details') }}</h6>
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

            <form action="{{ route('drivers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name">{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">{{ __('messages.Phone') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">{{ __('messages.Email') }}</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">{{ __('messages.Password') }} <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                          <div class="form-group">
                            <label>{{ __('Options') }}</label>
                            <div class="checkbox-list">
                                @foreach($options as $option)
                                    <label class="checkbox">
                                        <input type="checkbox" name="option_ids[]" value="{{ $option->id }}" 
                                        @if(isset($driver) && $driver->options->contains($option->id))
                                            checked
                                        @elseif(old('option_ids') && in_array($option->id, old('option_ids')))
                                            checked
                                        @endif
                                        >
                                        <span></span>{{ $option->name_en }} ({{ $option->name_ar }})
                                    </label>
                                @endforeach
                            </div>
                            @error('option_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="balance">{{ __('messages.Balance') }}</label>
                            <input type="number" step="0.01" class="form-control" id="balance" name="balance" value="{{ old('balance', 0) }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="activate">{{ __('messages.Status') }}</label>
                            <select class="form-control" id="activate" name="activate">
                                <option value="1" {{ old('activate', 1) == 1 ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                                <option value="2" {{ old('activate') == 2 ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Driver Photo -->
                        <div class="form-group">
                            <label for="photo">{{ __('messages.Driver_Photo') }}</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="photo" name="photo">
                                <label class="custom-file-label" for="photo">{{ __('messages.Choose_file') }}</label>
                            </div>
                            <div class="mt-3" id="photo-preview"></div>
                        </div>

                        <!-- Car Information -->
                        <h5 class="mt-4 mb-3">{{ __('messages.Car_Information') }}</h5>
                        
                        <div class="form-group">
                            <label for="model">{{ __('messages.Car_Model') }}</label>
                            <input type="text" class="form-control" id="model" name="model" value="{{ old('model') }}">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="production_year">{{ __('messages.Production_Year') }}</label>
                                    <input type="text" class="form-control" id="production_year" name="production_year" value="{{ old('production_year') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">{{ __('messages.Color') }}</label>
                                    <input type="text" class="form-control" id="color" name="color" value="{{ old('color') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="plate_number">{{ __('messages.Plate_Number') }}</label>
                            <input type="text" class="form-control" id="plate_number" name="plate_number" value="{{ old('plate_number') }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="photo_of_car">{{ __('messages.Car_Photo') }}</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="photo_of_car" name="photo_of_car">
                                <label class="custom-file-label" for="photo_of_car">{{ __('messages.Choose_file') }}</label>
                            </div>
                            <div class="mt-3" id="car-preview"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Documents Section -->
                <h5 class="mt-4 mb-3">{{ __('messages.Documents') }}</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="driving_license_front">{{ __('messages.Driving_License_Front') }}</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="driving_license_front" name="driving_license_front">
                                <label class="custom-file-label" for="driving_license_front">{{ __('messages.Choose_file') }}</label>
                            </div>
                            <div class="mt-3" id="driving-license-front-preview"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="car_license_front">{{ __('messages.Car_License_Front') }}</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="car_license_front" name="car_license_front">
                                <label class="custom-file-label" for="car_license_front">{{ __('messages.Choose_file') }}</label>
                            </div>
                            <div class="mt-3" id="car-license-front-preview"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="driving_license_back">{{ __('messages.Driving_License_Back') }}</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="driving_license_back" name="driving_license_back">
                                <label class="custom-file-label" for="driving_license_back">{{ __('messages.Choose_file') }}</label>
                            </div>
                            <div class="mt-3" id="driving-license-back-preview"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="car_license_back">{{ __('messages.Car_License_Back') }}</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="car_license_back" name="car_license_back">
                                <label class="custom-file-label" for="car_license_back">{{ __('messages.Choose_file') }}</label>
                            </div>
                            <div class="mt-3" id="car-license-back-preview"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('messages.Save') }}
                    </button>
                    <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
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
    // Show image previews
    $(document).ready(function() {
        // Show filename on file select
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
            
            // Image preview
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                let previewId = '';
                
                // Determine which preview to update
                switch(this.id) {
                    case 'photo':
                        previewId = 'photo-preview';
                        break;
                    case 'photo_of_car':
                        previewId = 'car-preview';
                        break;
                    case 'driving_license_front':
                        previewId = 'driving-license-front-preview';
                        break;
                    case 'driving_license_back':
                        previewId = 'driving-license-back-preview';
                        break;
                    case 'car_license_front':
                        previewId = 'car-license-front-preview';
                        break;
                    case 'car_license_back':
                        previewId = 'car-license-back-preview';
                        break;
                }
                
                if (previewId) {
                    reader.onload = function(e) {
                        $('#' + previewId).html('<img src="' + e.target.result + '" class="img-fluid img-thumbnail" style="max-height: 150px;">');
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    });
</script>
@endsection