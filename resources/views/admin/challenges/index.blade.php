@extends('layouts.admin')

@section('title', __('messages.Challenges'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Challenges') }}</h1>
        <a href="{{ route('challenges.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_Challenge') }}
        </a>
    </div>

    <!-- Search and Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('challenges.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{ __('messages.Search') }}</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="{{ __('messages.Search_By_Title') }}"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('messages.Challenge_Type') }}</label>
                            <select name="challenge_type" class="form-control">
                                <option value="">{{ __('messages.All') }}</option>
                                <option value="referral" {{ request('challenge_type') == 'referral' ? 'selected' : '' }}>
                                    {{ __('messages.challenge_type_referral') }}
                                </option>
                                <option value="trips" {{ request('challenge_type') == 'trips' ? 'selected' : '' }}>
                                    {{ __('messages.challenge_type_trips') }}
                                </option>
                                <option value="spending" {{ request('challenge_type') == 'spending' ? 'selected' : '' }}>
                                    {{ __('messages.challenge_type_spending') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('messages.Status') }}</label>
                            <select name="is_active" class="form-control">
                                <option value="">{{ __('messages.All') }}</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>
                                    {{ __('messages.Active') }}
                                </option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>
                                    {{ __('messages.Inactive') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> {{ __('messages.Search') }}
                                </button>
                                <a href="{{ route('challenges.index') }}" class="btn btn-secondary btn-block mt-1">
                                    <i class="fas fa-redo"></i> {{ __('messages.Reset') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Challenges Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Challenge_List') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Icon') }}</th>
                            <th>{{ __('messages.Title') }}</th>
                            <th>{{ __('messages.Type') }}</th>
                            <th>{{ __('messages.Target') }}</th>
                            <th>{{ __('messages.Reward') }}</th>
                            <th>{{ __('messages.Duration') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($challenges as $challenge)
                        <tr>
                            <td>{{ $challenge->id }}</td>
                            <td>
                                @if ($challenge->icon)
                                <img src="{{ asset('assets/admin/uploads/challenges/' . $challenge->icon) }}"
                                    alt="{{ $challenge->getTitle() }}" width="50" height="50" style="object-fit: cover;">
                                @else
                                <i class="fas fa-trophy fa-2x text-warning"></i>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $challenge->getTitle() }}</strong>
                                <br>
                                <small class="text-muted">{{ $challenge->getTitle('ar') }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $challenge->getChallengeTypeText() }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $challenge->target_count }}</strong>
                                @if($challenge->challenge_type == 'referral')
                                    {{ __('messages.persons') }}
                                @elseif($challenge->challenge_type == 'trips')
                                    {{ __('messages.trips') }}
                                @else
                                    {{ __('messages.currency') }}
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    {{ $challenge->reward_amount }} {{ __('messages.currency') }}
                                </span>
                            </td>
                            <td>
                                @if($challenge->start_date || $challenge->end_date)
                                    <small>
                                        @if($challenge->start_date)
                                        {{ __('messages.From') }}: {{ $challenge->start_date->format('Y-m-d') }}<br>
                                        @endif
                                        @if($challenge->end_date)
                                        {{ __('messages.To') }}: {{ $challenge->end_date->format('Y-m-d') }}
                                        @endif
                                    </small>
                                @else
                                    <span class="badge badge-secondary">{{ __('messages.No_Time_Limit') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($challenge->isCurrentlyActive())
                                <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @else
                                <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group-vertical" role="group">
                                    <a href="{{ route('challenges.show', $challenge->id) }}" 
                                       class="btn btn-info btn-sm mb-1" 
                                       title="{{ __('messages.View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('challenges.edit', $challenge->id) }}" 
                                       class="btn btn-primary btn-sm mb-1" 
                                       title="{{ __('messages.Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('challenges.participants', $challenge->id) }}" 
                                       class="btn btn-secondary btn-sm mb-1" 
                                       title="{{ __('messages.Participants') }}">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <form action="{{ route('challenges.destroy', $challenge->id) }}" 
                                          method="POST" 
                                          style="display: inline;"
                                          onsubmit="return confirm('{{ __('messages.Are_You_Sure') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="{{ __('messages.Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
                    {{ __('messages.Showing') }} {{ $challenges->firstItem() ?? 0 }} {{ __('messages.To') }}
                    {{ $challenges->lastItem() ?? 0 }} {{ __('messages.Of') }} {{ $challenges->total() }}
                    {{ __('messages.Entries') }}
                </div>
                <div>
                    {{ $challenges->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection