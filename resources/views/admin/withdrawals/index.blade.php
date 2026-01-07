@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>{{ __('messages.Pending_Withdrawal_Requests') }}</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>{{ __('messages.ID') }}</th>
                <th>{{ __('messages.Type') }}</th>
                <th>{{ __('messages.Name') }}</th>
                <th>{{ __('messages.Phone') }}</th>
                <th>{{ __('messages.Amount') }}</th>
                <th>{{ __('messages.Date') }}</th>
                <th>{{ __('messages.Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingRequests as $request)
                <tr>
                    <td>{{ $request->id }}</td>

                    <td>
                        {{ $request->user_id ? __('messages.User') : __('messages.Driver') }}
                    </td>

                    <td>
                        {{ $request->user_id ? $request->user->name : $request->driver->name }}
                    </td>

                    <td>
                        {{ $request->user_id ? $request->user->phone : $request->driver->phone }}
                    </td>

                    <td>{{ $request->amount }}</td>

                    <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>

                    <td>
                        {{-- History --}}
                        <a href="{{ $request->user_id 
                            ? route('admin.withdrawals.history', $request->user->id)
                            : route('admin.withdrawals.history', $request->driver->id) }}"
                           class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Approve --}}
                        <form method="POST"
                              action="{{ route('admin.withdrawals.approve', $request->id) }}"
                              style="display:inline;">
                            @csrf
                            <button type="submit"
                                    class="btn btn-success btn-sm"
                                    onclick="return confirm('{{ __('messages.Are_You_Sure_Approve') }}')">
                                {{ __('messages.Approve') }}
                            </button>
                        </form>

                        {{-- Reject --}}
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                data-toggle="modal"
                                data-target="#rejectModal{{ $request->id }}">
                            {{ __('messages.Reject') }}
                        </button>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST"
                                          action="{{ route('admin.withdrawals.reject', $request->id) }}">
                                        @csrf

                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                {{ __('messages.Reject_Withdrawal_Request') }}
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>{{ __('messages.Reason_For_Rejection') }}</label>
                                                <textarea name="note" class="form-control" required></textarea>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button"
                                                    class="btn btn-secondary"
                                                    data-dismiss="modal">
                                                {{ __('messages.Cancel') }}
                                            </button>
                                            <button type="submit"
                                                    class="btn btn-danger">
                                                {{ __('messages.Reject') }}
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                        {{-- End Modal --}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $pendingRequests->links() }}
</div>
@endsection
