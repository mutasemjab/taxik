@extends('layouts.admin')

@section('title', __('messages.Challenge_Details'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Challenge_Details') }}</h1>
        <div>
            <a href="{{ route('challenges.edit', $challenge->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
            </a>
            <a href="{{ route('challenges.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Challenge Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Challenge_Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        @if($challenge->icon)
                        <div class="col-md-12 text-center mb-3">
                            <img src="{{ asset('assets/admin/uploads/challenges/' . $challenge->icon) }}" 
                                 alt="{{ $challenge->getTitle() }}" 
                                 style="max-width: 150px; max-height: 150px;">
                        </div>
                        @endif
                    </div>

                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">{{ __('messages.ID') }}</th>
                            <td>{{ $challenge->id }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Title_English') }}</th>
                            <td>{{ $challenge->title_en }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Title_Arabic') }}</th>
                            <td>{{ $challenge->title_ar }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Description_English') }}</th>
                            <td>{{ $challenge->description_en }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Description_Arabic') }}</th>
                            <td>{{ $challenge->description_ar }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Challenge_Type') }}</th>
                            <td>
                                <span class="badge badge-info badge-lg">
                                    {{ $challenge->getChallengeTypeText() }} / {{ $challenge->getChallengeTypeText('ar') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Target_Count') }}</th>
                            <td>
                                <strong class="text-primary">{{ $challenge->target_count }}</strong>
                                @if($challenge->challenge_type == 'referral')
                                    {{ __('messages.persons') }}
                                @elseif($challenge->challenge_type == 'trips')
                                    {{ __('messages.trips') }}
                                @else
                                    {{ __('messages.currency') }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Reward_Amount') }}</th>
                            <td>
                                <strong class="text-success">{{ $challenge->reward_amount }} {{ __('messages.currency') }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Max_Completions_Per_User') }}</th>
                            <td>{{ $challenge->max_completions_per_user }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Start_Date') }}</th>
                            <td>{{ $challenge->start_date ? $challenge->start_date->format('Y-m-d') : __('messages.No_Limit') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.End_Date') }}</th>
                            <td>{{ $challenge->end_date ? $challenge->end_date->format('Y-m-d') : __('messages.No_Limit') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Status') }}</th>
                            <td>
                                @if($challenge->isCurrentlyActive())
                                <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @else
                                <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Created_At') }}</th>
                            <td>{{ $challenge->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Updated_At') }}</th>
                            <td>{{ $challenge->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-lg-4">
            <!-- Statistics Cards -->
            <div class="card border-left-primary shadow mb-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Total_Participants') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_participants'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-left-success shadow mb-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Total_Completions') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_completions'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-left-warning shadow mb-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('messages.In_Progress') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['in_progress'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-left-info shadow mb-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('messages.Total_Rewards_Given') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_rewards_given'] }} {{ __('messages.currency') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Quick_Actions') }}</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('challenges.participants', $challenge->id) }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-users"></i> {{ __('messages.View_Participants') }}
                    </a>
                    <a href="{{ route('challenges.edit', $challenge->id) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> {{ __('messages.Edit_Challenge') }}
                    </a>
                    <form action="{{ route('challenges.destroy', $challenge->id) }}" method="POST" 
                          onsubmit="return confirm('{{ __('messages.Are_You_Sure') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> {{ __('messages.Delete_Challenge') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection