@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-history"></i> {{ __('dashboard.activity_logs') }} - {{ class_basename($model) }} #{{ $model->id }}
            </h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info mb-4">
                <strong>{{ __('dashboard.viewing_logs_for') }}:</strong> {{ class_basename($model) }}
                @if(isset($model->name))
                    - {{ $model->name }}
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th width="20%">{{ __('dashboard.date_time') }}</th>
                            <th width="12%">{{ __('dashboard.event') }}</th>
                            <th width="18%">{{ __('dashboard.user') }}</th>
                            <th>{{ __('dashboard.changes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <div><strong>{{ $log->created_at->format('Y-m-d H:i:s') }}</strong></div>
                                <span class="badge bg-info text-white mt-1">{{ $log->created_at->diffForHumans() }}</span>
                            </td>
                            <td>
                                @if($log->event == 'created')
                                    <span class="badge bg-success"><i class="fas fa-plus-circle"></i> {{ __('dashboard.event_created') }}</span>
                                @elseif($log->event == 'updated')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-edit"></i> {{ __('dashboard.event_updated') }}</span>
                                @elseif($log->event == 'deleted')
                                    <span class="badge bg-danger"><i class="fas fa-trash"></i> {{ __('dashboard.event_deleted') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($log->event) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->causer)
                                    <div><i class="fas fa-user-circle"></i> <strong>{{ $log->causer->name }}</strong></div>
                                    <small class="text-muted">{{ $log->causer->email ?? '' }}</small>
                                @else
                                    <span class="text-muted"><i class="fas fa-robot"></i> {{ __('dashboard.system') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->event == 'updated' && $log->properties->has('old') && $log->properties->has('attributes'))
                                    <div class="changes-list">
                                        @foreach($log->properties['attributes'] as $key => $value)
                                            @if(isset($log->properties['old'][$key]) && $log->properties['old'][$key] != $value)
                                                <div class="change-item mb-2 p-2 border-start border-3 border-primary bg-light">
                                                    <strong class="text-primary">{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                    <div class="ms-3">
                                                        <span class="badge bg-danger">{{ __('dashboard.old') }}:</span>
                                                        <code>{{ is_array($log->properties['old'][$key]) ? json_encode($log->properties['old'][$key]) : ($log->properties['old'][$key] ?? 'null') }}</code>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-success">{{ __('dashboard.new') }}:</span>
                                                        <code>{{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}</code>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @elseif($log->event == 'created')
                                    <span class="text-success"><i class="fas fa-check-circle"></i> {{ __('dashboard.new_record_created') }}</span>
                                    @if($log->properties->has('attributes'))
                                        <div class="mt-2">
                                            <small class="text-muted">{{ __('dashboard.initial_values') }}:</small>
                                            <div class="ms-3">
                                                @foreach($log->properties['attributes'] as $key => $value)
                                                    @if($value)
                                                        <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @elseif($log->event == 'deleted')
                                    <span class="text-danger"><i class="fas fa-times-circle"></i> {{ __('dashboard.record_deleted') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="py-5">
                                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                    <p class="text-muted h5">{{ __('dashboard.no_logs_for_record') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('dashboard.back') }}
                </a>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
