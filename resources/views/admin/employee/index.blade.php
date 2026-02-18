@extends('layouts.admin')

@section('title', __('messages.employees'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ __('messages.employees') }}</h3>
                        @can('employee-add')
                            <a href="{{ route('admin.employee.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ __('messages.Add New Employee') }}
                            </a>
                        @endcan
                    </div>

                    <div class="card-body">
                        <!-- Search and Filter Form -->
                        <form method="GET" action="{{ route('admin.employee.index') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="{{ __('messages.Search') }}" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-search"></i> {{ __('messages.Search') }}
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.employee.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> {{ __('messages.Reset') }}
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Employees Table -->
                        @can('employee-table')
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('messages.Name') }}</th>
                                            <th>{{ __('messages.Username') }}</th>
                                            <th>{{ __('messages.Roles') }}</th>
                                            <th>{{ __('messages.Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data as $employee)
                                            <tr>
                                                <td>{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                                <td>
                                                    <strong>{{ $employee->name }}</strong>
                                                </td>
                                                <td>{{ $employee->username }}</td>
                                                <td>
                                                    @if ($employee->roles->count() > 0)
                                                        <div class="flex-wrap">
                                                            @foreach ($employee->roles as $role)
                                                                <span
                                                                    class="badge bg-info me-1 mb-1">{{ $role->name }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted">{{ __('messages.No roles') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        @can('employee-edit')
                                                            <a href="{{ route('admin.employee.edit', $employee->id) }}"
                                                                class="btn btn-sm btn-warning" title="{{ __('messages.Edit') }}">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endcan
                                                        @can('employee-delete')
                                                            <form action="{{ route('admin.employee.destroy', $employee->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                    title="{{ __('messages.Delete') }}"
                                                                    onclick="return confirm('{{ __('messages.Are you sure you want to delete this employee?') }}')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">{{ __('messages.No employees found') }}
                                                </td>
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
