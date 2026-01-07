@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>{{ __('messages.Withdrawal_Request_History') }}</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>{{ __('messages.ID') }}</th>
                <th>{{ __('messages.Type') }}</th>
                <th>{{ __('messages.Name') }}</th>
                <th>{{ __('messages.Amount') }}</th>
                <th>{{ __('messages.Status') }}</th>
                <th>{{ __('messages.Processed_By') }}</th>
                <th>{{ __('messages.Note') }}</th>
                <th>{{ __('messages.Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($processedRequests as $request)
                <tr>
                    <td>{{ $request->id }}</td>

                    <td>
                        {{ $request->user_id ? __('messages.User') : __('messages.Driver') }}
                    </td>

                    <td>
                        {{ $request->user_id ? $request->user->name : $request->driver->name }}
                    </td>

                    <td>{{ $request->amount }}</td>

                    <td>
                        @if($request->status == 2)
                            <span class="badge badge-success">
                                {{ __('messages.Approved') }}
                            </span>
                        @else
                            <span class="badge badge-danger">
                                {{ __('messages.Rejected') }}
                            </span>
                        @endif
                    </td>

                    <td>{{ $request->admin->name ?? __('messages.N_A') }}</td>

                    <td>{{ $request->note }}</td>

                    <td>{{ $request->updated_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $processedRequests->links() }}
</div>
@endsection
