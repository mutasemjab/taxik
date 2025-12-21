@extends('layouts.admin')

@section('title', __('messages.Add New Employee'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.Add New Employee') }}</h3>
                    <a href="{{ route('admin.employee.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('messages.Back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.employee.store') }}">
                        @csrf

                        <!-- Employee Name -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="{{ __('messages.Enter employee name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">{{ __('messages.Email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}"
                                       placeholder="{{ __('messages.Enter employee email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Username -->
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

                        <!-- Role Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">{{ __('messages.Roles') }} <span class="text-danger">*</span></label>
                                <div class="card">
                                    <div class="card-body">
                                        @foreach($roles as $role)
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="radio" name="role_id"
                                                       id="role_{{ $role->id }}" value="{{ $role->id }}"
                                                       {{ old('role_id') == $role->id ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="role_{{ $role->id }}">
                                                    <strong>{{ $role->name }}</strong>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('role_id')
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
                                <a href="{{ route('admin.employee.index') }}" class="btn btn-secondary ms-2">
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