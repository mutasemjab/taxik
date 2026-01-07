@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.complaints') }}</h1>
     
    </div>


    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.complaints') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Subject') }}</th>
                            <th>{{ __('messages.User') }}</th>
                            <th>{{ __('messages.Driver') }}</th>
                            <th>{{ __('messages.Order') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Created_At') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $complaint)
                        <tr>
                            <td>{{ $complaint->id }}</td>
                            <td>{{ $complaint->subject }}</td>
                            <td>{{ $complaint->user ? $complaint->user->name : __('messages.Not_Available') }}</td>
                            <td>{{ $complaint->driver ? $complaint->driver->name : __('messages.Not_Available') }}</td>
                            <td>{{ $complaint->order ? $complaint->order->id : __('messages.Not_Available') }}</td>
                            <td>
                                <span class="badge badge-{{ $complaint->status_badge }}">
                                    {{ $complaint->status_label }}
                                </span>
                            </td>
                            <td>{{ $complaint->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                             
                                <form action="{{ route('complaints.destroy', $complaint) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('messages.Are_You_Sure') }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('messages.No_Complaints_Found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $complaints->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "ordering": false,
            "paging": false,
            "info": false,
            "searching": true,
        });
    });
</script>
@endpush