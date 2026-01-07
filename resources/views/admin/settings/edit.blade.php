@extends('layouts.admin')

@section('title')
    {{ __('messages.Edit') }} {{ __('messages.Settings') }}
@endsection

@section('contentheaderlink')
    <a href="{{ route('settings.index') }}">
        {{ __('messages.Settings') }}
    </a>
@endsection

@section('contentheaderactive')
    {{ __('messages.Edit') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                {{ __('messages.Edit') }} {{ __('messages.Settings') }}
            </h3>
        </div>

        <div class="card-body">
            <form action="{{ route('settings.update', $data['id']) }}" method="POST">
                <div class="row">
                    @csrf
                    @method('PUT')
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Key') }}</label>

                            <input type="text" class="form-control" value="{{ __('messages.' . $data['key']) }}"
                                disabled>

                            <input type="hidden" name="key" value="{{ $data['key'] }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Value') }}</label>
                            <input type="text" name="value" class="form-control"
                                value="{{ old('value', $data['value']) }}">
                            @error('value')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-sm">
                                {{ __('messages.Update') }}
                            </button>
                            <a href="{{ route('settings.index') }}" class="btn btn-sm btn-danger">
                                {{ __('messages.Cancel') }}
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection
