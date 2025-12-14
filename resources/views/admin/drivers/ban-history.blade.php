@extends('layouts.admin')

@section('title', __('messages.Ban_History'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-history"></i> {{ __('messages.Ban_History') }} - {{ $driver->name }}
        </h1>
        <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_To_Drivers') }}
        </a>
    </div>

    <!-- Driver Info Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            @if($driver->photo)
                            <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}" alt="{{ $driver->name }}" class="img-fluid rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                            <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="{{ __('messages.No_Image') }}" class="img-fluid rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                            @endif
                        </div>
                        <div class="col-md-10">
                            <h4>{{ $driver->name }}</h4>
                            <p class="mb-1">
                                <i class="fas fa-phone"></i> {{ $driver->country_code }} {{ $driver->phone }}
                                @if($driver->email)
                                | <i class="fas fa-envelope"></i> {{ $driver->email }}
                                @endif
                            </p>
                            <p class="mb-0">
                                <strong>{{ __('messages.Current_Status') }}:</strong>
                                @if($driver->activate == 1)
                                <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @elseif($driver->activate == 2)
                                <span class="badge badge-danger">{{ __('messages.Banned') }}</span>
                                @else
                                <span class="badge badge-warning">{{ __('messages.Waiting_Approve') }}</span>
                                @endif
                                
                                <strong class="ml-3">{{ __('messages.Total_Bans') }}:</strong> <span class="badge badge-info">{{ $driver->bans->count() }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ban History Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> {{ __('messages.Complete_Ban_History') }}
            </h6>
        </div>
        <div class="card-body">
            @if($driver->bans->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="banHistoryTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.Ban_ID') }}</th>
                            <th>{{ __('messages.Banned_By') }}</th>
                            <th>{{ __('messages.Reason') }}</th>
                            <th>{{ __('messages.Type') }}</th>
                            <th>{{ __('messages.Banned_At') }}</th>
                            <th>{{ __('messages.Ban_Until') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($driver->bans()->latest()->get() as $ban)
                        <tr class="{{ $ban->is_active ? 'table-danger' : '' }}">
                            <td>{{ $ban->id }}</td>
                            <td>
                                @if($ban->admin)
                                    {{ $ban->admin->name }}
                                @else
                                    <span class="text-muted">{{ __('messages.System') }}</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ __('messages.ban_reason_' . $ban->ban_reason) }}</strong>
                                @if($ban->ban_description)
                                <br><small class="text-muted">{{ Str::limit($ban->ban_description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($ban->is_permanent)
                                <span class="badge badge-danger">{{ __('messages.Permanent') }}</span>
                                @else
                                <span class="badge badge-warning">{{ __('messages.Temporary') }}</span>
                                @endif
                            </td>
                            <td>{{ $ban->banned_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($ban->is_permanent)
                                <span class="text-danger">{{ __('messages.Never') }}</span>
                                @elseif($ban->ban_until)
                                {{ $ban->ban_until->format('Y-m-d H:i') }}
                                @else
                                <span class="text-muted">{{ __('messages.N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($ban->is_active)
                                    @if($ban->is_permanent)
                                    <span class="badge badge-danger">{{ __('messages.Active') }} ({{ __('messages.Permanent') }})</span>
                                    @elseif($ban->isExpired())
                                    <span class="badge badge-secondary">{{ __('messages.Expired') }}</span>
                                    @else
                                    <span class="badge badge-danger">{{ __('messages.Active') }} ({{ $ban->getRemainingTime() }} {{ __('messages.Left') }})</span>
                                    @endif
                                @else
                                <span class="badge badge-success">{{ __('messages.Lifted') }}</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#banDetailModal{{ $ban->id }}">
                                    <i class="fas fa-eye"></i> {{ __('messages.Details') }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> {{ __('messages.Driver_Never_Banned') }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Ban Detail Modals -->
@foreach($driver->bans as $ban)
<div class="modal fade" id="banDetailModal{{ $ban->id }}" tabindex="-1" role="dialog" aria-labelledby="banDetailModalLabel{{ $ban->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header {{ $ban->is_active ? 'bg-danger text-white' : 'bg-secondary text-white' }}">
                <h5 class="modal-title" id="banDetailModalLabel{{ $ban->id }}">
                    <i class="fas fa-ban"></i> {{ __('messages.Ban_Details') }} #{{ $ban->id }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">{{ __('messages.Ban_Information') }}</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">{{ __('messages.Status') }}:</th>
                                <td>
                                    @if($ban->is_active)
                                    <span class="badge badge-danger">{{ $ban->getStatusText() }}</span>
                                    @else
                                    <span class="badge badge-success">{{ $ban->getStatusText() }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Type') }}:</th>
                                <td>
                                    @if($ban->is_permanent)
                                    <span class="badge badge-danger">{{ __('messages.Permanent') }}</span>
                                    @else
                                    <span class="badge badge-warning">{{ __('messages.Temporary') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Reason') }}:</th>
                                <td><strong>{{ __('messages.ban_reason_' . $ban->ban_reason) }}</strong></td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Banned_By') }}:</th>
                                <td>
                                    @if($ban->admin)
                                    {{ $ban->admin->name }}
                                    @else
                                    <span class="text-muted">{{ __('messages.System') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Banned_At') }}:</th>
                                <td>{{ $ban->banned_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @if(!$ban->is_permanent)
                            <tr>
                                <th>{{ __('messages.Ban_Until') }}:</th>
                                <td>{{ $ban->ban_until ? $ban->ban_until->format('Y-m-d H:i:s') : __('messages.N/A') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Remaining_Time') }}:</th>
                                <td>{{ $ban->getRemainingTime() }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        @if(!$ban->is_active)
                        <h6 class="text-success">{{ __('messages.Unban_Information') }}</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">{{ __('messages.Unbanned_At') }}:</th>
                                <td>{{ $ban->unbanned_at ? $ban->unbanned_at->format('Y-m-d H:i:s') : __('messages.N/A') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Unbanned_By') }}:</th>
                                <td>
                                    @if($ban->unbannedByAdmin)
                                    {{ $ban->unbannedByAdmin->name }}
                                    @else
                                    <span class="text-muted">{{ __('messages.System_Auto') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($ban->unban_reason)
                            <tr>
                                <th>{{ __('messages.Unban_Reason') }}:</th>
                                <td>{{ $ban->unban_reason }}</td>
                            </tr>
                            @endif
                        </table>
                        @endif
                    </div>
                </div>
                
                @if($ban->ban_description)
                <hr>
                <h6 class="text-primary">{{ __('messages.Additional_Details') }}</h6>
                <div class="alert alert-secondary">
                    {{ $ban->ban_description }}
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#banHistoryTable').DataTable({
            "order": [[0, "desc"]]
        });
    });
</script>
@endsection