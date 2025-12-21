@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-history"></i> Activity Logs - {{ class_basename($model) }} #{{ $model->id }}
            </h3>
        </div>
        
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <strong>Viewing logs for:</strong> {{ class_basename($model) }} 
                @if(isset($model->name))
                    - {{ $model->name }}
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th width="20%">Date & Time</th>
                            <th width="12%">Event</th>
                            <th width="18%">User</th>
                            <th>Changes</th>
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
                                    <span class="badge bg-success"><i class="fas fa-plus-circle"></i> Created</span>
                                @elseif($log->event == 'updated')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-edit"></i> Updated</span>
                                @elseif($log->event == 'deleted')
                                    <span class="badge bg-danger"><i class="fas fa-trash"></i> Deleted</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($log->event) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->causer)
                                    <div><i class="fas fa-user-circle"></i> <strong>{{ $log->causer->name }}</strong></div>
                                    <small class="text-muted">{{ $log->causer->email ?? '' }}</small>
                                @else
                                    <span class="text-muted"><i class="fas fa-robot"></i> System</span>
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
                                                        <span class="badge bg-danger">Old:</span>
                                                        <code>{{ is_array($log->properties['old'][$key]) ? json_encode($log->properties['old'][$key]) : ($log->properties['old'][$key] ?? 'null') }}</code>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-success">New:</span>
                                                        <code>{{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}</code>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @elseif($log->event == 'created')
                                    <span class="text-success"><i class="fas fa-check-circle"></i> New record created</span>
                                    @if($log->properties->has('attributes'))
                                        <div class="mt-2">
                                            <small class="text-muted">Initial values:</small>
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
                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Record deleted</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="py-5">
                                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                    <p class="text-muted h5">No activity logs found for this record</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
