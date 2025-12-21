@extends('layouts.admin')

@section('title', __('messages.Edit Role'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.Edit Role') }} - {{ $data->name }}</h3>
                    <a href="{{ route('admin.role.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('messages.Back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.role.update', $data->id) }}">
                        @csrf
                        @method('PATCH')

                        <!-- Role Name -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $data->name) }}"
                                       placeholder="{{ __('messages.Enter role name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Permissions Section -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">{{ __('messages.Permissions') }}</h4>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-success select-all-perms">
                                    <i class="fas fa-check-double"></i> {{ __('messages.Select All') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger unselect-all-perms ms-2">
                                    <i class="fas fa-times-circle"></i> {{ __('messages.Unselect All') }}
                                </button>
                            </div>
                        </div>

                        @foreach($groupedPermissions as $resource => $permissions)
                            @if(count($permissions) > 0)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 text-capitalize">{{ ucfirst($resource) }}</h5>
                                        <button type="button" class="btn btn-sm btn-outline-primary check-all-btn" data-resource="{{ $resource }}">
                                            <i class="fas fa-check-square"></i> {{ __('messages.Check All') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <tbody>
                                                @foreach($permissions as $permission)
                                                <tr>
                                                    <td width="50">
                                                        <input type="checkbox" name="perms[]"
                                                               value="{{ $permission->id }}"
                                                               id="perm_{{ $permission->id }}"
                                                               class="resource-{{ $resource }}"
                                                               {{ in_array($permission->id, $role_permissions) ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <label for="perm_{{ $permission->id }}" class="mb-0">
                                                            <strong>{{ $permission->name }}</strong>
                                                        </label>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach

                        @error('perms')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('messages.Update') }}
                                </button>
                                <a href="{{ route('admin.role.index') }}" class="btn btn-secondary ms-2">
                                    <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Check All buttons per resource
    document.querySelectorAll('.check-all-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const resource = this.dataset.resource;
            const checkboxes = document.querySelectorAll(`.resource-${resource}`);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);

            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });

            // Update button text
            this.innerHTML = !allChecked ?
                '<i class="fas fa-square"></i> {{ __("messages.Uncheck All") }}' :
                '<i class="fas fa-check-square"></i> {{ __("messages.Check All") }}';
        });
    });

    // Handle Select All Permissions button
    document.querySelector('.select-all-perms').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('input[name="perms[]"]').forEach(cb => {
            cb.checked = true;
        });
    });

    // Handle Unselect All Permissions button
    document.querySelector('.unselect-all-perms').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('input[name="perms[]"]').forEach(cb => {
            cb.checked = false;
        });
    });
});
</script>
@endpush
@endsection