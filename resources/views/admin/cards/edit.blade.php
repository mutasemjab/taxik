@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.edit_card') }}</h4>
                    <a href="{{ route('cards.index') }}" class="btn btn-secondary">
                        {{ __('messages.back_to_list') }}
                    </a>
                </div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('cards.update', $card) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="pos_id" class="form-label">{{ __('messages.pos') }}</label>
                            <select class="form-control @error('pos_id') is-invalid @enderror" 
                                    id="pos_id" 
                                    name="pos_id">
                                <option value="">{{ __('messages.select_pos') }}</option>
                                @foreach($posRecords as $pos)
                                    <option value="{{ $pos->id }}" 
                                            {{ old('pos_id', $card->pos_id) == $pos->id ? 'selected' : '' }}>
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
                                   value="{{ old('name', $card->name) }}" 
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
                                   value="{{ old('price', $card->price) }}" 
                                   placeholder="{{ __('messages.enter_price') }}"
                                   step="0.01"
                                   min="0"
                                   required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="number_of_cards" class="form-label">{{ __('messages.number_of_cards') }} <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('number_of_cards') is-invalid @enderror" 
                                   id="number_of_cards" 
                                   name="number_of_cards" 
                                   value="{{ old('number_of_cards', $card->number_of_cards) }}" 
                                   placeholder="{{ __('messages.enter_number_of_cards') }}"
                                   min="1"
                                   max="10000"
                                   required>
                            @error('number_of_cards')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('messages.number_of_cards_help') }}</div>
                        </div>

                        @if($card->number_of_cards != old('number_of_cards', $card->number_of_cards))
                            <div class="alert alert-warning">
                                <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.number_change_warning') }}
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <strong>{{ __('messages.current_stats') }}:</strong><br>
                            {{ __('messages.current_card_numbers') }}: {{ $card->cardNumbers->count() }}<br>
                            {{ __('messages.active_numbers') }}: {{ $card->active_card_numbers_count }}<br>
                            {{ __('messages.used_numbers') }}: {{ $card->used_card_numbers_count }}
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('cards.index') }}" class="btn btn-secondary me-md-2">
                                {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection