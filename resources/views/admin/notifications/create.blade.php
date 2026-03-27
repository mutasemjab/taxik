@extends('layouts.admin')
@section('title')
{{ __('messages.Send_Notification') }}
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title card_title_center">{{ __('messages.Send_Notification') }}</h3>
    </div>
    <div class="card-body">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        @if(session('message'))
                            <div class="alert alert-success">{{ session('message') }}</div>
                        @endif

                        <form action="{{ route('notifications.send') }}" method="post" id="notification-form">
                            @csrf

                            <div class="form-group mt-0">
                                <label for="title">{{ __('messages.Title') }}</label>
                                <input type="text"
                                       class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}">
                                @error('title')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="body">{{ __('messages.Body') }}</label>
                                <textarea name="body" id="body"
                                          class="form-control @error('body') is-invalid @enderror"
                                          rows="4">{{ old('body') }}</textarea>
                                @error('body')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="type">{{ __('messages.Notification_Type') }}</label>
                                <select name="type" id="type" class="form-control" onchange="toggleTargetField()">
                                    <option value="0" {{ old('type') == '0' ? 'selected' : '' }}>{{ __('messages.All_Users_And_Drivers') }}</option>
                                    <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>{{ __('messages.Only_Users') }}</option>
                                    <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>{{ __('messages.Only_Drivers') }}</option>
                                    <option value="3" {{ old('type') == '3' ? 'selected' : '' }}>{{ __('messages.Specific_User') }}</option>
                                    <option value="4" {{ old('type') == '4' ? 'selected' : '' }}>{{ __('messages.Specific_Driver') }}</option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Specific User --}}
                            <div class="form-group" id="specific-user-field" style="display:none;">
                                <label for="target_id_user">{{ __('messages.Select_User') }}</label>
                                <select name="target_id" id="target_id_user" class="form-control">
                                    <option value="">-- {{ __('messages.Select_User') }} --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('target_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('target_id')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Specific Driver --}}
                            <div class="form-group" id="specific-driver-field" style="display:none;">
                                <label for="target_id_driver">{{ __('messages.Select_Driver') }}</label>
                                <select name="target_id" id="target_id_driver" class="form-control">
                                    <option value="">-- {{ __('messages.Select_Driver') }} --</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ old('target_id') == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->name }} ({{ $driver->phone }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="text-right mt-3">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    <i class="fas fa-paper-plane"></i> {{ __('messages.Send_Notification') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTargetField() {
    var type = document.getElementById('type').value;
    document.getElementById('specific-user-field').style.display   = (type === '3') ? 'block' : 'none';
    document.getElementById('specific-driver-field').style.display = (type === '4') ? 'block' : 'none';

    // Remove name from hidden selects to avoid sending both
    document.getElementById('target_id_user').name   = (type === '3') ? 'target_id' : '';
    document.getElementById('target_id_driver').name = (type === '4') ? 'target_id' : '';
}
// Run on page load to restore state after validation error
document.addEventListener('DOMContentLoaded', toggleTargetField);
</script>
@endsection
