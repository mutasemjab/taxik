@extends('layouts.admin')

@section('title', __('messages.view_config'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('messages.view_config') }}</h4>
                    <div>
                        <a href="{{ route('app-configs.edit', $appConfig) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> {{ __('messages.edit') }}
                        </a>
                        <a href="{{ route('app-configs.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> {{ __('messages.basic_information') }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th class="bg-light" width="30%">{{ __('messages.id') }}</th>
                                        <td>{{ $appConfig->id }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.email') }}</th>
                                        <td>
                                            @if($appConfig->email)
                                                <a href="mailto:{{ $appConfig->email }}">{{ $appConfig->email }}</a>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.phone') }}</th>
                                        <td>
                                            @if($appConfig->phone)
                                                <a href="tel:{{ $appConfig->phone }}">{{ $appConfig->phone }}</a>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.created_at') }}</th>
                                        <td>{{ $appConfig->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.updated_at') }}</th>
                                        <td>{{ $appConfig->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- User App Configuration -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user"></i> {{ __('messages.user_app_configuration') }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th class="bg-light" width="30%">{{ __('messages.google_play_link_user_app') }}</th>
                                        <td>
                                            @if($appConfig->google_play_link_user_app)
                                                <a href="{{ $appConfig->google_play_link_user_app }}" target="_blank" class="btn btn-sm btn-success">
                                                    <i class="fab fa-google-play"></i> {{ __('messages.open_link') }}
                                                </a>
                                                <br><small class="text-muted">{{ $appConfig->google_play_link_user_app }}</small>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.app_store_link_user_app') }}</th>
                                        <td>
                                            @if($appConfig->app_store_link_user_app)
                                                <a href="{{ $appConfig->app_store_link_user_app }}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fab fa-app-store"></i> {{ __('messages.open_link') }}
                                                </a>
                                                <br><small class="text-muted">{{ $appConfig->app_store_link_user_app }}</small>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.hawawi_link_user_app') }}</th>
                                        <td>
                                            @if($appConfig->hawawi_link_user_app)
                                                <a href="{{ $appConfig->hawawi_link_user_app }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-mobile-alt"></i> {{ __('messages.open_link') }}
                                                </a>
                                                <br><small class="text-muted">{{ $appConfig->hawawi_link_user_app }}</small>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.min_version_google_play_user_app') }}</th>
                                        <td>
                                            @if($appConfig->min_version_google_play_user_app)
                                                <span class="badge bg-success">{{ $appConfig->min_version_google_play_user_app }}</span>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.min_version_app_store_user_app') }}</th>
                                        <td>
                                            @if($appConfig->min_version_app_store_user_app)
                                                <span class="badge bg-primary">{{ $appConfig->min_version_app_store_user_app }}</span>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.min_version_hawawi_user_app') }}</th>
                                        <td>
                                            @if($appConfig->min_version_hawawi_user_app)
                                                <span class="badge bg-info">{{ $appConfig->min_version_hawawi_user_app }}</span>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Driver App Configuration -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-car"></i> {{ __('messages.driver_app_configuration') }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th class="bg-light" width="30%">{{ __('messages.google_play_link_driver_app') }}</th>
                                        <td>
                                            @if($appConfig->google_play_link_driver_app)
                                                <a href="{{ $appConfig->google_play_link_driver_app }}" target="_blank" class="btn btn-sm btn-success">
                                                    <i class="fab fa-google-play"></i> {{ __('messages.open_link') }}
                                                </a>
                                                <br><small class="text-muted">{{ $appConfig->google_play_link_driver_app }}</small>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.app_store_link_driver_app') }}</th>
                                        <td>
                                            @if($appConfig->app_store_link_driver_app)
                                                <a href="{{ $appConfig->app_store_link_driver_app }}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fab fa-app-store"></i> {{ __('messages.open_link') }}
                                                </a>
                                                <br><small class="text-muted">{{ $appConfig->app_store_link_driver_app }}</small>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.hawawi_link_driver_app') }}</th>
                                        <td>
                                            @if($appConfig->hawawi_link_driver_app)
                                                <a href="{{ $appConfig->hawawi_link_driver_app }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-mobile-alt"></i> {{ __('messages.open_link') }}
                                                </a>
                                                <br><small class="text-muted">{{ $appConfig->hawawi_link_driver_app }}</small>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.min_version_google_play_driver_app') }}</th>
                                        <td>
                                            @if($appConfig->min_version_google_play_driver_app)
                                                <span class="badge bg-success">{{ $appConfig->min_version_google_play_driver_app }}</span>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.min_version_app_store_driver_app') }}</th>
                                        <td>
                                            @if($appConfig->min_version_app_store_driver_app)
                                                <span class="badge bg-primary">{{ $appConfig->min_version_app_store_driver_app }}</span>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('messages.min_version_hawawi_driver_app') }}</th>
                                        <td>
                                            @if($appConfig->min_version_hawawi_driver_app)
                                                <span class="badge bg-info">{{ $appConfig->min_version_hawawi_driver_app }}</span>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('app-configs.edit', $appConfig) }}" class="btn btn-warning me-md-2">
                                    <i class="fas fa-edit"></i> {{ __('messages.edit') }}
                                </a>
                                <form action="{{ route('app-configs.destroy', $appConfig) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> {{ __('messages.delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection