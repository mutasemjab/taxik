@extends('layouts.admin')

@section('title', __('messages.Challenge_Participants'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            {{ __('messages.Challenge_Participants') }}: {{ $challenge->getTitle() }}
        </h1>
        <div>
            <a href="{{ route('challenges.show', $challenge->id) }}" class="btn btn-info">
                <i class="fas fa-info-circle"></i> {{ __('messages.Challenge_Details') }}
            </a>
            <a href="{{ route('challenges.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back') }}
            </a>
        </div>
    </div>

    <!-- Challenge Summary -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>{{ __('messages.Challenge_Type') }}:</strong>
                    <span class="badge badge-info">{{ $challenge->getChallengeTypeText() }}</span>
                </div>
                <div class="col-md-3">
                    <strong>{{ __('messages.Target') }}:</strong>
                    {{ $challenge->target_count }}
                </div>
                <div class="col-md-3">
                    <strong>{{ __('messages.Reward') }}:</strong>
                    <span class="text-success">{{ $challenge->reward_amount }} {{ __('messages.currency') }}</span>
                </div>
                <div class="col-md-3">
                    <strong>{{ __('messages.Total_Participants') }}:</strong>
                    {{ $participants->total() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Participants Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Participant_List') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.User') }}</th>
                            <th>{{ __('messages.Progress') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Times_Completed') }}</th>
                            <th>{{ __('messages.Last_Completed') }}</th>
                            <th>{{ __('messages.Started_At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($participants as $progress)
                        <tr>
                            <td>{{ $progress->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($progress->user->photo)
                                    <img src="{{ asset('assets/admin/uploads/' . $progress->user->photo) }}" 
                                         class="rounded-circle mr-2" 
                                         width="40" height="40" 
                                         style="object-fit: cover;">
                                    @else
                                    <img src="{{ asset('assets/admin/img/no-image.png') }}" 
                                         class="rounded-circle mr-2" 
                                         width="40" height="40">
                                    @endif
                                    <div>
                                        <strong>{{ $progress->user->name }}</strong><br>
                                        <small class="text-muted">{{ $progress->user->phone }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar {{ $progress->is_completed ? 'bg-success' : 'bg-primary' }}" 
                                         role="progressbar" 
                                         style="width: {{ $progress->getProgressPercentage() }}%">
                                        {{ $progress->current_count }} / {{ $challenge->target_count }}
                                    </div>
                                </div>
                                <small class="text-muted">{{ number_format($progress->getProgressPercentage(), 1) }}%</small>
                            </td>
                            <td>
                                @if($progress->is_completed)
                                <span class="badge badge-success">{{ __('messages.Completed') }}</span>
                                @elseif($progress->current_count > 0)
                                <span class="badge badge-warning">{{ __('messages.In_Progress') }}</span>
                                @else
                                <span class="badge badge-secondary">{{ __('messages.Not_Started') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $progress->times_completed }}x</span>
                                @if($progress->times_completed >= $challenge->max_completions_per_user)
                                <br><small class="text-danger">{{ __('messages.Max_Reached') }}</small>
                                @endif
                            </td>
                            <td>
                                @if($progress->completed_at)
                                {{ $progress->completed_at->format('Y-m-d H:i') }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $progress->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i> {{ __('messages.No_Participants_Yet') }}
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($participants->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    {{ __('messages.Showing') }} {{ $participants->firstItem() ?? 0 }} {{ __('messages.To') }}
                    {{ $participants->lastItem() ?? 0 }} {{ __('messages.Of') }} {{ $participants->total() }}
                    {{ __('messages.Entries') }}
                </div>
                <div>
                    {{ $participants->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection