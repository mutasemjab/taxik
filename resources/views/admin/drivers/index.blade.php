@extends('layouts.admin')

@section('title', __('messages.Drivers'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Drivers') }}</h1>
        <a href="{{ route('drivers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_Driver') }}
        </a>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Driver_List') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Photo') }}</th>
                            <th>{{ __('messages.Name') }}</th>
                            <th>{{ __('messages.Phone') }}</th>
                            <th>{{ __('messages.Car') }}</th>
                            <th>{{ __('messages.Balance') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drivers as $driver)
                        <tr>
                            <td>{{ $driver->id }}</td>
                            <td>
                                @if($driver->photo)
                                <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}" alt="{{ $driver->name }}" width="50">
                                @else
                                <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="{{ __('messages.No_Image') }}" width="50">
                                @endif
                            </td>
                            <td>
                                {{ $driver->name }}
                                @if($driver->activate == 2 && $driver->activeBan)
                                    <br>
                                    <small class="text-danger">
                                        <i class="fas fa-ban"></i> 
                                        {{ $driver->activeBan->is_permanent ? __('messages.Banned_Permanently') : __('messages.Banned_Until') . ' ' . $driver->activeBan->ban_until->format('Y-m-d H:i') }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $driver->country_code }} {{ $driver->phone }}</td>
                            <td>
                                {{ $driver->model ?? __('messages.N/A') }}
                                @if($driver->color)
                                <span class="badge badge-info">{{ $driver->color }}</span>
                                @endif
                            </td>
                            <td>{{ $driver->balance }}</td>
                            <td>
                                @if($driver->activate == 1)
                                <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @elseif($driver->activate == 2)
                                <span class="badge badge-danger">{{ __('messages.Banned') }}</span>
                                @elseif($driver->activate == 3)
                                <span class="badge badge-warning">{{ __('messages.Waiting_Approve') }}</span>
                                @endif
                            </td>
                             <td>
                                <div class="d-flex flex-wrap">
                                    <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-info btn-sm mr-1 mb-1" title="{{ __('messages.View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-primary btn-sm mr-1 mb-1" title="{{ __('messages.Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm mr-1 mb-1" data-toggle="modal" data-target="#topUpModal{{ $driver->id }}" title="{{ __('messages.Top_Up_Balance') }}">
                                        <i class="fas fa-wallet"></i>
                                    </button>
                                    <a href="{{ route('drivers.transactions', $driver->id) }}" class="btn btn-secondary btn-sm mr-1 mb-1" title="{{ __('messages.Transactions') }}">
                                        <i class="fas fa-money-bill"></i>
                                    </a>
                                    
                                    @if($driver->activate == 2)
                                        <!-- Unban Button -->
                                        <button type="button" class="btn btn-warning btn-sm mr-1 mb-1" data-toggle="modal" data-target="#unbanModal{{ $driver->id }}" title="{{ __('messages.Unban_Driver') }}">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    @else
                                        <!-- Ban Button -->
                                        <a href="{{ route('drivers.ban.form', $driver->id) }}" class="btn btn-danger btn-sm mr-1 mb-1" title="{{ __('messages.Ban_Driver') }}">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    @endif
                                    
                                    <!-- Ban History Button -->
                                    <a href="{{ route('drivers.ban.history', $driver->id) }}" class="btn btn-dark btn-sm mr-1 mb-1" title="{{ __('messages.Ban_History') }}">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top Up Modals -->
@foreach($drivers as $driver)
<div class="modal fade" id="topUpModal{{ $driver->id }}" tabindex="-1" role="dialog" aria-labelledby="topUpModalLabel{{ $driver->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="topUpModalLabel{{ $driver->id }}">{{ __('messages.Top_Up_Balance_For') }}: {{ $driver->name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('drivers.topUp', $driver->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-4">
                        @if($driver->photo)
                        <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}" alt="{{ $driver->name }}" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                        <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="{{ __('messages.No_Image') }}" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        @endif
                        <h5 class="mt-2">{{ $driver->name }}</h5>
                        <h6>{{ __('messages.Current_Balance') }}: <span class="text-primary">{{ $driver->balance }}</span></h6>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount{{ $driver->id }}">{{ __('messages.Amount') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount{{ $driver->id }}" name="amount" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="note{{ $driver->id }}">{{ __('messages.Note') }}</label>
                        <textarea class="form-control" id="note{{ $driver->id }}" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.Add_To_Balance') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Unban Modals -->
@foreach($drivers as $driver)
@if($driver->activate == 2)
<div class="modal fade" id="unbanModal{{ $driver->id }}" tabindex="-1" role="dialog" aria-labelledby="unbanModalLabel{{ $driver->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="unbanModalLabel{{ $driver->id }}">
                    <i class="fas fa-unlock"></i> {{ __('messages.Unban_Driver') }}: {{ $driver->name }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('drivers.unban', $driver->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($driver->activeBan)
                    <div class="alert alert-info">
                        <strong>{{ __('messages.Current_Ban_Info') }}:</strong><br>
                        <strong>{{ __('messages.Reason') }}:</strong> {{ $driver->activeBan->getReasonText() }}<br>
                        <strong>{{ __('messages.Type') }}:</strong> {{ $driver->activeBan->is_permanent ? __('messages.Permanent') : __('messages.Temporary') }}<br>
                        @if(!$driver->activeBan->is_permanent)
                        <strong>{{ __('messages.Until') }}:</strong> {{ $driver->activeBan->ban_until->format('Y-m-d H:i') }}<br>
                        <strong>{{ __('messages.Remaining') }}:</strong> {{ $driver->activeBan->getRemainingTime() }}
                        @endif
                    </div>
                    @endif
                    
                    <div class="form-group">
                        <label for="unban_reason{{ $driver->id }}">{{ __('messages.Unban_Reason') }} ({{ __('messages.Optional') }})</label>
                        <textarea class="form-control" id="unban_reason{{ $driver->id }}" name="unban_reason" rows="3" placeholder="{{ __('messages.Enter_Reason_For_Unbanning') }}"></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> {{ __('messages.Unban_Confirmation_Message') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-unlock"></i> {{ __('messages.Unban_Driver') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
@endsection