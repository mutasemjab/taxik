
@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Withdrawal Request History</h2>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Name</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Processed By</th>
                <th>Note</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($processedRequests as $request)
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
                    <td>{{ $request->amount }}</td>
                    <td>
                        @if($request->status == 2)
                            <span class="badge badge-success">Approved</span>
                        @else
                            <span class="badge badge-danger">Rejected</span>
                        @endif
                    </td>
                    <td>{{ $request->admin->name ?? 'N/A' }}</td>
                    <td>{{ $request->note }}</td>
                    <td>{{ $request->updated_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    {{ $processedRequests->links() }}
</div>
@endsection