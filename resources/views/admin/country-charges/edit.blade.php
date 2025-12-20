@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.Edit Country Charge') }}</h3>
                </div>
                <form action="{{ route('country-charges.update', $countryCharge->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        
                        <div class="form-group">
                            <label for="country_name">{{ __('messages.Country Name') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('country_name') is-invalid @enderror" 
                                   id="country_name" 
                                   name="country_name" 
                                   value="{{ old('country_name', $countryCharge->name) }}" 
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
                            @foreach($countryCharge->chargeData as $index => $data)
                                <div class="charge-data-row card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                                                    <input type="text" 
                                                           class="form-control @error('charge_data.'.$index.'.name') is-invalid @enderror" 
                                                           name="charge_data[{{ $index }}][name]" 
                                                           value="{{ old('charge_data.'.$index.'.name', $data->name) }}"
                                                           placeholder="{{ __('messages.Enter name') }}"
                                                           required>
                                                    @error('charge_data.'.$index.'.name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('messages.Phone') }} <span class="text-danger">*</span></label>
                                                    <input type="text" 
                                                           class="form-control @error('charge_data.'.$index.'.phone') is-invalid @enderror" 
                                                           name="charge_data[{{ $index }}][phone]" 
                                                           value="{{ old('charge_data.'.$index.'.phone', $data->phone) }}"
                                                           placeholder="{{ __('messages.Enter phone') }}"
                                                           required>
                                                    @error('charge_data.'.$index.'.phone')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('messages.Cliq Name') }} <span class="text-danger">*</span></label>
                                                    <input type="text" 
                                                           class="form-control @error('charge_data.'.$index.'.cliq_name') is-invalid @enderror" 
                                                           name="charge_data[{{ $index }}][cliq_name]" 
                                                           value="{{ old('charge_data.'.$index.'.cliq_name', $data->cliq_name) }}"
                                                           placeholder="{{ __('messages.Enter cliq name') }}"
                                                           required>
                                                    @error('charge_data.'.$index.'.cliq_name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
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
                            @endforeach
                        </div>

                        @error('charge_data')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('messages.Update') }}
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
let chargeDataIndex = {{ $countryCharge->chargeData->count() }};

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