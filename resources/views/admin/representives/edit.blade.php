@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.Edit Representative') }}</h3>
                </div>
                <form action="{{ route('representives.update', $representive->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $representive->name) }}" 
                                   placeholder="{{ __('messages.Enter representative name') }}"
                                   required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">{{ __('messages.Phone') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $representive->phone) }}" 
                                   placeholder="{{ __('messages.Enter phone number') }}"
                                   required>
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="commission">{{ __('messages.Commission') }} (%) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('commission') is-invalid @enderror" 
                                   id="commission" 
                                   name="commission" 
                                   value="{{ old('commission', $representive->commission) }}" 
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   placeholder="{{ __('messages.Enter commission percentage') }}"
                                   required>
                            @error('commission')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('messages.Commission percentage (0-100)') }}
                            </small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('messages.Update') }}
                        </button>
                        <a href="{{ route('representives.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection