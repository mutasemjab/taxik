@extends('layouts.admin')

@section('title', __('messages.Add New Admin'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.Add New Admin') }}</h3>
                    <a href="{{ route('admin.admin.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('messages.Back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.admin.store') }}">
                        @csrf

                        <!-- Admin Name -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="{{ __('messages.Enter admin name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">{{ __('messages.Email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}"
                                       placeholder="{{ __('messages.Enter admin email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Username & Password -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="username" class="form-label">{{ __('messages.Username') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror"
                                       id="username" name="username" value="{{ old('username') }}"
                                       placeholder="{{ __('messages.Enter username') }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">{{ __('messages.Password') }} <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password"
                                       placeholder="{{ __('messages.Enter password') }}" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Super Admin Toggle -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('messages.Super Admin') }} <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_super" id="is_super_yes" value="1"
                                           {{ old('is_super') == '1' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="is_super_yes">
                                        {{ __('messages.Yes') }}
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_super" id="is_super_no" value="0"
                                           {{ old('is_super') == '0' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="is_super_no">
                                        {{ __('messages.No') }}
                                    </label>
                                </div>
                                @error('is_super')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('messages.Save') }}
                                </button>
                                <a href="{{ route('admin.admin.index') }}" class="btn btn-secondary ms-2">
                                    <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection