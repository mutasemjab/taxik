@extends('layouts.admin')

@section('title', __('messages.Users'))

@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Users') }}</h1>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('messages.Add_New_User') }}
            </a>
        </div>

        <!-- Search and Filter Section -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('users.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('messages.Search') }}</label>
                                <input type="text" name="search" class="form-control"
                                    placeholder="{{ __('messages.Search_By_Name_Phone_Email') }}"
                                    value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ __('messages.Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="">{{ __('messages.All') }}</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>
                                        {{ __('messages.Active') }}</option>
                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>
                                        {{ __('messages.Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ __('messages.Min_Balance') }}</label>
                                <input type="number" name="min_balance" class="form-control" step="0.01"
                                    value="{{ request('min_balance') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ __('messages.Max_Balance') }}</label>
                                <input type="number" name="max_balance" class="form-control" step="0.01"
                                    value="{{ request('max_balance') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> {{ __('messages.Search') }}
                                    </button>
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-block mt-1">
                                        <i class="fas fa-redo"></i> {{ __('messages.Reset') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.User_List') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('messages.ID') }}</th>
                                <th>{{ __('messages.Photo') }}</th>
                                <th>{{ __('messages.Name') }}</th>
                                <th>{{ __('messages.Phone') }}</th>
                                <th>{{ __('messages.Email') }}</th>
                                <th>{{ __('messages.Balance') }}</th>
                                <th>{{ __('messages.Status') }}</th>
                                <th>{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        @if ($user->photo)
                                            <img src="{{ asset('assets/admin/uploads/' . $user->photo) }}"
                                                alt="{{ $user->name }}" width="50">
                                        @else
                                            <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No Image"
                                                width="50">
                                        @endif
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->country_code }} {{ $user->phone }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->balance }}</td>
                                    <td>
                                        @if ($user->activate == 1)
                                            <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                                data-target="#topUpModal{{ $user->id }}">
                                                <i class="fas fa-wallet"></i>
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        {{ __('messages.Showing') }} {{ $users->firstItem() ?? 0 }} {{ __('messages.To') }}
                        {{ $users->lastItem() ?? 0 }} {{ __('messages.Of') }} {{ $users->total() }}
                        {{ __('messages.Entries') }}
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($users as $user)
        <div class="modal fade" id="topUpModal{{ $user->id }}" tabindex="-1" role="dialog"
            aria-labelledby="topUpModalLabel{{ $user->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="topUpModalLabel{{ $user->id }}">
                            {{ __('messages.Top_Up_Balance_For') }}: {{ $user->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('users.topUp', $user->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                @if ($user->photo)
                                    <img src="{{ asset('assets/admin/uploads/' . $user->photo) }}"
                                        alt="{{ $user->name }}" class="img-profile rounded-circle"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No Image"
                                        class="img-profile rounded-circle"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                @endif
                                <h5 class="mt-2">{{ $user->name }}</h5>
                                <h6>{{ __('messages.Current_Balance') }}: <span
                                        class="text-primary">{{ $user->balance }}</span></h6>
                            </div>

                            <div class="form-group">
                                <label for="amount{{ $user->id }}">{{ __('messages.Amount') }} <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount{{ $user->id }}"
                                    name="amount" step="0.01" min="0.01" required>
                            </div>

                            <div class="form-group">
                                <label for="note{{ $user->id }}">{{ __('messages.Note') }}</label>
                                <textarea class="form-control" id="note{{ $user->id }}" name="note" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('messages.Close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('messages.Add_To_Balance') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
