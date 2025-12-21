@extends('layouts.admin')

@section('title', __('messages.Admins'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.Admins') }}</h3>
                    @can('admin-add')
                        <a href="{{ route('admin.admin.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('messages.Add New Admin') }}
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.admin.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control"
                                       placeholder="{{ __('messages.Search') }}"
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search"></i> {{ __('messages.Search') }}
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.admin.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> {{ __('messages.Reset') }}
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Admins Table -->
                    @can('admin-table')
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('messages.Name') }}</th>
                                        <th>{{ __('messages.Email') }}</th>
                                        <th>{{ __('messages.Username') }}</th>
                                        <th>{{ __('messages.Super Admin') }}</th>
                                        <th>{{ __('messages.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $admin)
                                        <tr>
                                            <td>{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $admin->name }}</strong>
                                            </td>
                                            <td>{{ $admin->email }}</td>
                                            <td>{{ $admin->username }}</td>
                                            <td>
                                                @if($admin->is_super == 1)
                                                    <span class="badge bg-danger">{{ __('messages.Yes') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('messages.No') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @can('admin-edit')
                                                        <a href="{{ route('admin.admin.edit', $admin->id) }}"
                                                           class="btn btn-sm btn-warning" title="{{ __('messages.Edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('admin-delete')
                                                        @if($admin->id != 1)
                                                            <form action="{{ route('admin.admin.destroy', $admin->id) }}" method="POST" style="display:inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                        title="{{ __('messages.Delete') }}"
                                                                        onclick="return confirm('{{ __('messages.Are you sure you want to delete this admin?') }}')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button class="btn btn-sm btn-danger disabled" disabled title="{{ __('messages.Cannot delete super admin') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">{{ __('messages.No admins found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $data->appends(request()->query())->links() }}
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection