@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.create_card') }}</h4>
                    <a href="{{ route('cards.index') }}" class="btn btn-secondary">
                        {{ __('messages.back_to_list') }}
                    </a>
                </div>

                <div class="card-body">
                   
                    <form action="{{ route('cards.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="pos_id" class="form-label">{{ __('messages.pos') }}</label>
                            <select class="form-control @error('pos_id') is-invalid @enderror" 
                                    id="pos_id" 
                                    name="pos_id">
                                <option value="">{{ __('messages.select_pos') }}</option>
                                @foreach($posRecords as $pos)
                                    <option value="{{ $pos->id }}" {{ old('pos_id') == $pos->id ? 'selected' : '' }}>
                                        {{ $pos->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pos_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="{{ __('messages.enter_card_name') }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('price') is-invalid @enderror" 
                                   id="price" 
                                   name="price" 
                                   value="{{ old('price') }}" 
                                   placeholder="{{ __('messages.enter_price') }}"
                                   step="any"
                                   min="0"
                                   required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                         
                        <div class="mb-3">
                            <label for="price" class="form-label">{{ __('messages.pos_commission_percentage') }} <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('pos_commission_percentage') is-invalid @enderror" 
                                   id="pos_commission_percentage" 
                                   name="pos_commission_percentage" 
                                   value="{{ old('pos_commission_percentage') }}" 
                                   placeholder="{{ __('messages.enter_pos_commission_percentage') }}"
                                   step="any"
                                   min="0"
                                   required>
                            @error('pos_commission_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="driver_recharge_amount" class="form-label">{{ __('messages.driver_recharge_amount') }} <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('driver_recharge_amount') is-invalid @enderror" 
                                   id="driver_recharge_amount" 
                                   name="driver_recharge_amount" 
                                   value="{{ old('driver_recharge_amount') }}" 
                                   placeholder="{{ __('messages.enter_driver_recharge_amount') }}"
                                   step="any"
                                   min="0"
                                   required>
                            @error('driver_recharge_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="number_of_cards" class="form-label">{{ __('messages.number_of_cards') }} <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('number_of_cards') is-invalid @enderror" 
                                   id="number_of_cards" 
                                   name="number_of_cards" 
                                   value="{{ old('number_of_cards') }}" 
                                   placeholder="{{ __('messages.enter_number_of_cards') }}"
                                   min="1"
                                   max="10000"
                                   required>
                            @error('number_of_cards')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('messages.number_of_cards_help') }}</div>
                        </div>

                        <div class="alert alert-info">
                            <strong>{{ __('messages.note') }}:</strong> {{ __('messages.card_generation_note') }}
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('cards.index') }}" class="btn btn-secondary me-md-2">
                                {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.create_and_generate') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection