
@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Pending Withdrawal Requests</h2>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingRequests as $request)
                <tr>
                    <td>{{ $request->id }}</td>
                    <td>
                        @if($request->user_id)
                            User
                        @else
                            Driver
                        @endif
                    </td>
                    <td>
                        @if($request->user_id)
                            {{ $request->user->name }}
                        @else
                            {{ $request->driver->name }}
                        @endif
                    </td>
                    <td>
                        @if($request->user_id)
                            {{ $request->user->phone }}
                        @else
                            {{ $request->driver->phone }}
                        @endif
                    </td>
                    <td>{{ $request->amount }}</td>
                    <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                         @if($request->user_id)
                        <a href="{{ route('admin.withdrawals.history', $request->user->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                         </a>
                        @else
                         <a href="{{ route('admin.withdrawals.history', $request->driver->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                         </a>
                        @endif
                     

                        <form method="POST" action="{{ route('admin.withdrawals.approve', $request->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this withdrawal?')">Approve</button>
                        </form>
                        
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal{{ $request->id }}">
                            Reject
                        </button>
                        
                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.withdrawals.reject', $request->id) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Withdrawal Request</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="note">Reason for rejection</label>
                                                <textarea class="form-control" id="note" name="note" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    {{ $pendingRequests->links() }}
</div>
@endsection