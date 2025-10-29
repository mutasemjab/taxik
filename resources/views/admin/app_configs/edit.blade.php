@extends('layouts.admin')

@section('title', __('messages.edit_config'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('messages.edit_config') }}</h4>
                    <a href="{{ route('app-configs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('app-configs.update', $appConfig) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('messages.email') }}</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $appConfig->email) }}"
                                           placeholder="{{ __('messages.enter_email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">{{ __('messages.phone') }}</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $appConfig->phone) }}"
                                           placeholder="{{ __('messages.enter_phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- User App Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-user"></i> {{ __('messages.user_app_configuration') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- User App Google Play Link -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="google_play_link_user_app" class="form-label">{{ __('messages.google_play_link_user_app') }}</label>
                                            <input type="url" 
                                                   class="form-control @error('google_play_link_user_app') is-invalid @enderror" 
                                                   id="google_play_link_user_app" 
                                                   name="google_play_link_user_app" 
                                                   value="{{ old('google_play_link_user_app', $appConfig->google_play_link_user_app) }}"
                                                   placeholder="{{ __('messages.enter_google_play_link_user_app') }}">
                                            @error('google_play_link_user_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- User App App Store Link -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="app_store_link_user_app" class="form-label">{{ __('messages.app_store_link_user_app') }}</label>
                                            <input type="url" 
                                                   class="form-control @error('app_store_link_user_app') is-invalid @enderror" 
                                                   id="app_store_link_user_app" 
                                                   name="app_store_link_user_app" 
                                                   value="{{ old('app_store_link_user_app', $appConfig->app_store_link_user_app) }}"
                                                   placeholder="{{ __('messages.enter_app_store_link_user_app') }}">
                                            @error('app_store_link_user_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- User App Hawawi Link -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="hawawi_link_user_app" class="form-label">{{ __('messages.hawawi_link_user_app') }}</label>
                                            <input type="url" 
                                                   class="form-control @error('hawawi_link_user_app') is-invalid @enderror" 
                                                   id="hawawi_link_user_app" 
                                                   name="hawawi_link_user_app" 
                                                   value="{{ old('hawawi_link_user_app', $appConfig->hawawi_link_user_app) }}"
                                                   placeholder="{{ __('messages.enter_hawawi_link_user_app') }}">
                                            @error('hawawi_link_user_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- User App Minimum Versions -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="min_version_google_play_user_app" class="form-label">{{ __('messages.min_version_google_play_user_app') }}</label>
                                            <input type="text" 
                                                   class="form-control @error('min_version_google_play_user_app') is-invalid @enderror" 
                                                   id="min_version_google_play_user_app" 
                                                   name="min_version_google_play_user_app" 
                                                   value="{{ old('min_version_google_play_user_app', $appConfig->min_version_google_play_user_app) }}"
                                                   placeholder="{{ __('messages.enter_version') }}">
                                            @error('min_version_google_play_user_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="min_version_app_store_user_app" class="form-label">{{ __('messages.min_version_app_store_user_app') }}</label>
                                            <input type="text" 
                                                   class="form-control @error('min_version_app_store_user_app') is-invalid @enderror" 
                                                   id="min_version_app_store_user_app" 
                                                   name="min_version_app_store_user_app" 
                                                   value="{{ old('min_version_app_store_user_app', $appConfig->min_version_app_store_user_app) }}"
                                                   placeholder="{{ __('messages.enter_version') }}">
                                            @error('min_version_app_store_user_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="min_version_hawawi_user_app" class="form-label">{{ __('messages.min_version_hawawi_user_app') }}</label>
                                            <input type="text" 
                                                   class="form-control @error('min_version_hawawi_user_app') is-invalid @enderror" 
                                                   id="min_version_hawawi_user_app" 
                                                   name="min_version_hawawi_user_app" 
                                                   value="{{ old('min_version_hawawi_user_app', $appConfig->min_version_hawawi_user_app) }}"
                                                   placeholder="{{ __('messages.enter_version') }}">
                                            @error('min_version_hawawi_user_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Driver App Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-car"></i> {{ __('messages.driver_app_configuration') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Driver App Google Play Link -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="google_play_link_driver_app" class="form-label">{{ __('messages.google_play_link_driver_app') }}</label>
                                            <input type="url" 
                                                   class="form-control @error('google_play_link_driver_app') is-invalid @enderror" 
                                                   id="google_play_link_driver_app" 
                                                   name="google_play_link_driver_app" 
                                                   value="{{ old('google_play_link_driver_app', $appConfig->google_play_link_driver_app) }}"
                                                   placeholder="{{ __('messages.enter_google_play_link_driver_app') }}">
                                            @error('google_play_link_driver_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Driver App App Store Link -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="app_store_link_driver_app" class="form-label">{{ __('messages.app_store_link_driver_app') }}</label>
                                            <input type="url" 
                                                   class="form-control @error('app_store_link_driver_app') is-invalid @enderror" 
                                                   id="app_store_link_driver_app" 
                                                   name="app_store_link_driver_app" 
                                                   value="{{ old('app_store_link_driver_app', $appConfig->app_store_link_driver_app) }}"
                                                   placeholder="{{ __('messages.enter_app_store_link_driver_app') }}">
                                            @error('app_store_link_driver_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Driver App Hawawi Link -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="hawawi_link_driver_app" class="form-label">{{ __('messages.hawawi_link_driver_app') }}</label>
                                            <input type="url" 
                                                   class="form-control @error('hawawi_link_driver_app') is-invalid @enderror" 
                                                   id="hawawi_link_driver_app" 
                                                   name="hawawi_link_driver_app" 
                                                   value="{{ old('hawawi_link_driver_app', $appConfig->hawawi_link_driver_app) }}"
                                                   placeholder="{{ __('messages.enter_hawawi_link_driver_app') }}">
                                            @error('hawawi_link_driver_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Driver App Minimum Versions -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="min_version_google_play_driver_app" class="form-label">{{ __('messages.min_version_google_play_driver_app') }}</label>
                                            <input type="text" 
                                                   class="form-control @error('min_version_google_play_driver_app') is-invalid @enderror" 
                                                   id="min_version_google_play_driver_app" 
                                                   name="min_version_google_play_driver_app" 
                                                   value="{{ old('min_version_google_play_driver_app', $appConfig->min_version_google_play_driver_app) }}"
                                                   placeholder="{{ __('messages.enter_version') }}">
                                            @error('min_version_google_play_driver_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="min_version_app_store_driver_app" class="form-label">{{ __('messages.min_version_app_store_driver_app') }}</label>
                                            <input type="text" 
                                                   class="form-control @error('min_version_app_store_driver_app') is-invalid @enderror" 
                                                   id="min_version_app_store_driver_app" 
                                                   name="min_version_app_store_driver_app" 
                                                   value="{{ old('min_version_app_store_driver_app', $appConfig->min_version_app_store_driver_app) }}"
                                                   placeholder="{{ __('messages.enter_version') }}">
                                            @error('min_version_app_store_driver_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="min_version_hawawi_driver_app" class="form-label">{{ __('messages.min_version_hawawi_driver_app') }}</label>
                                            <input type="text" 
                                                   class="form-control @error('min_version_hawawi_driver_app') is-invalid @enderror" 
                                                   id="min_version_hawawi_driver_app" 
                                                   name="min_version_hawawi_driver_app" 
                                                   value="{{ old('min_version_hawawi_driver_app', $appConfig->min_version_hawawi_driver_app) }}"
                                                   placeholder="{{ __('messages.enter_version') }}">
                                            @error('min_version_hawawi_driver_app')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('app-configs.index') }}" class="btn btn-secondary me-md-2">
                                {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('messages.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection