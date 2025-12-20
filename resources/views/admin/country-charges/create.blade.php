@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.Add Country Charge') }}</h3>
                </div>
                <form action="{{ route('country-charges.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                            <div class="form-group">
                            <label for="country_name">{{ __('messages.Country Name') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('country_name') is-invalid @enderror" 
                                   id="country_name" 
                                   name="country_name" 
                                   value="{{ old('country_name') }}" 
                                   placeholder="{{ __('messages.Enter country name') }}"
                                   required>
                            @error('country_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>{{ __('messages.Charge Data') }}</h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="addChargeData()">
                                <i class="fas fa-plus"></i> {{ __('messages.Add Charge Data') }}
                            </button>
                        </div>

                        <div id="charge-data-container">
                            <!-- Initial charge data row -->
                            <div class="charge-data-row card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('charge_data.0.name') is-invalid @enderror" 
                                                       name="charge_data[0][name]" 
                                                       value="{{ old('charge_data.0.name') }}"
                                                       placeholder="{{ __('messages.Enter name') }}"
                                                       required>
                                                @error('charge_data.0.name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('messages.Phone') }} <span class="text-danger">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('charge_data.0.phone') is-invalid @enderror" 
                                                       name="charge_data[0][phone]" 
                                                       value="{{ old('charge_data.0.phone') }}"
                                                       placeholder="{{ __('messages.Enter phone') }}"
                                                       required>
                                                @error('charge_data.0.phone')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('messages.Service_provider') }} <span class="text-danger">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('charge_data.0.service_provider') is-invalid @enderror" 
                                                       name="charge_data[0][service_provider]" 
                                                       value="{{ old('charge_data.0.service_provider') }}"
                                                       placeholder="{{ __('messages.Enter service_provider') }}"
                                                       required>
                                                @error('charge_data.0.service_provider')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('messages.Cliq Name') }} <span class="text-danger">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('charge_data.0.cliq_name') is-invalid @enderror" 
                                                       name="charge_data[0][cliq_name]" 
                                                       value="{{ old('charge_data.0.cliq_name') }}"
                                                       placeholder="{{ __('messages.Enter cliq name') }}"
                                                       required>
                                                @error('charge_data.0.cliq_name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-block" onclick="removeChargeData(this)" disabled>
                                                <i class="fas fa-trash"></i> {{ __('messages.Remove') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('charge_data')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('messages.Save') }}
                        </button>
                        <a href="{{ route('country-charges.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let chargeDataIndex = 1;

function addChargeData() {
    const container = document.getElementById('charge-data-container');
    const newRow = `
        <div class="charge-data-row card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="charge_data[${chargeDataIndex}][name]" placeholder="{{ __('messages.Enter name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('messages.Phone') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="charge_data[${chargeDataIndex}][phone]" placeholder="{{ __('messages.Enter phone') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('messages.Service_provider') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="charge_data[${chargeDataIndex}][service_provider]" placeholder="{{ __('messages.Enter service_provider') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('messages.Cliq Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="charge_data[${chargeDataIndex}][cliq_name]" placeholder="{{ __('messages.Enter cliq name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-block" onclick="removeChargeData(this)">
                            <i class="fas fa-trash"></i> {{ __('messages.Remove') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newRow);
    chargeDataIndex++;
    updateRemoveButtons();
}

function removeChargeData(button) {
    button.closest('.charge-data-row').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.charge-data-row');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('button[onclick*="removeChargeData"]');
        if (rows.length === 1) {
            removeBtn.disabled = true;
        } else {
            removeBtn.disabled = false;
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
});
</script>
@endpush
@endsection