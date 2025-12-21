@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Activity Logs</h3>
        </div>
        
        <!-- Filter Form -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Model Type</label>
                    <select name="log_name" class="form-control">
                        <option value="">All Models</option>
                        <option value="card" {{ request('log_name') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="user" {{ request('log_name') == 'user' ? 'selected' : '' }}>User</option>
                        <option value="pos" {{ request('log_name') == 'pos' ? 'selected' : '' }}>POS</option>
                        <!-- Add more model types as needed -->
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Event Type</label>
                    <select name="event" class="form-control">
                        <option value="">All Events</option>
                        <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="15%">Date & Time</th>
                            <th width="10%">Model</th>
                            <th width="10%">Event</th>
                            <th width="15%">User</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <div>{{ $log->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                <div><span class="badge bg-info text-white">{{ $log->created_at->diffForHumans() }}</span></div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($log->log_name) }}</span>
                                @if($log->subject)
                                    <div><small class="text-muted">ID: {{ $log->subject_id }}</small></div>
                                @endif
                            </td>
                            <td>
                                @if($log->event == 'created')
                                    <span class="badge bg-success">Created</span>
                                @elseif($log->event == 'updated')
                                    <span class="badge bg-warning text-dark">Updated</span>
                                @elseif($log->event == 'deleted')
                                    <span class="badge bg-danger">Deleted</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($log->event) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->causer)
                                    <i class="fas fa-user"></i> {{ $log->causer->name }}
                                    <div><small class="text-muted">{{ $log->causer->email ?? '' }}</small></div>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>
                                @if($log->description)
                                    <div><strong>{{ $log->description }}</strong></div>
                                @endif
                                
                                @if($log->event == 'updated' && $log->properties->has('old') && $log->properties->has('attributes'))
                                    <small>
                                        @foreach($log->properties['attributes'] as $key => $value)
                                            @if(isset($log->properties['old'][$key]) && $log->properties['old'][$key] != $value)
                                                <div class="mb-1">
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                    <span class="text-danger">{{ is_array($log->properties['old'][$key]) ? json_encode($log->properties['old'][$key]) : ($log->properties['old'][$key] ?? 'null') }}</span>
                                                    →
                                                    <span class="text-success">{{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </small>
                                @elseif($log->event == 'created')
                                    <small class="text-muted">New record created</small>
                                @elseif($log->event == 'deleted')
                                    <small class="text-muted">Record deleted</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No activity logs found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

