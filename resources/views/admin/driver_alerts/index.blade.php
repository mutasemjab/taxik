@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2>{{ __('messages.driver_alerts') }}</h2>


    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>{{ __('messages.ID') }}</th>
                <th>{{ __('messages.Driver') }}</th>
                <th>{{ __('messages.Reason') }}</th>
                <th>{{ __('messages.Latitude') }}</th>
                <th>{{ __('messages.Longitude') }}</th>
                <th>{{ __('messages.address') }}</th>
                <th>{{ __('messages.Note') }}</th>
                <th>{{ __('messages.Status') }}</th>
                <th>{{ __('messages.Created_At') }}</th>
                <th>{{ __('messages.Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alerts as $alert)
            <tr>
                <td>{{ $alert->id }}</td>
                <td>{{ $alert->driver->name ?? 'N/A' }}</td>
                <td>{{ $alert->report }}</td>
                <td>{{ $alert->lat }}</td>
                <td>{{ $alert->lng }}</td>
                <td>{{ $alert->address }}</td>
                <td>{{ $alert->note }}</td>
                <td>
                    <form action="{{ route('admin.driver_alerts.updateStatus', $alert->id) }}" method="POST">
                        @csrf
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="pending" {{ $alert->status == 'pending' ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                            <option value="done" {{ $alert->status == 'done' ? 'selected' : '' }}>{{ __('messages.Done') }}</option>
                        </select>
                    </form>
                </td>
                <td>{{ $alert->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    <form action="{{ route('admin.driver_alerts.destroy', $alert->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('{{ __('messages.Delete_Confirm') }}')">{{ __('messages.Delete') }}</button>
                    </form>

                    <form action="{{ route('admin.driver_alerts.notify', $alert->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button class="btn btn-primary btn-sm">{{ __('messages.Notify_Nearby') }}</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
