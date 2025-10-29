@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Ratings') }}</h1>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Total_Ratings') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Average_Rating') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['average'] }}/5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('messages.Five_Star_Ratings') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['five_star'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('messages.One_Star_Ratings') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistics['one_star'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Distribution Chart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Rating_Distribution') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    @foreach([5,4,3,2,1] as $star)
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" style="min-width: 60px;">{{ $star }} <i class="fas fa-star text-warning"></i></span>
                            <div class="progress flex-grow-1" style="height: 25px;">
                                @php
                                    $starCount = $statistics[$star == 5 ? 'five_star' : ($star == 4 ? 'four_star' : ($star == 3 ? 'three_star' : ($star == 2 ? 'two_star' : 'one_star')))];
                                    $percentage = $statistics['total'] > 0 ? round(($starCount / $statistics['total']) * 100) : 0;
                                @endphp
                                <div class="progress-bar bg-{{ $star >= 4 ? 'success' : ($star == 3 ? 'warning' : 'danger') }}" 
                                     role="progressbar" 
                                     style="width: {{ $percentage }}%;" 
                                     aria-valuenow="{{ $percentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $percentage }}%
                                </div>
                            </div>
                            <span class="ml-2" style="min-width: 40px;">{{ $starCount }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Filters') }}</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('ratings.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="rating">{{ __('messages.Rating') }}</label>
                            <select class="form-control" id="rating" name="rating">
                                <option value="">{{ __('messages.All') }}</option>
                                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 {{ __('messages.Stars') }}</option>
                                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 {{ __('messages.Stars') }}</option>
                                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 {{ __('messages.Stars') }}</option>
                                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 {{ __('messages.Stars') }}</option>
                                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 {{ __('messages.Star') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="search">{{ __('messages.Search') }}</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="{{ __('messages.Search_by_user_driver_or_review') }}" 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> {{ __('messages.Filter') }}
                                </button>
                                <a href="{{ route('ratings.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> {{ __('messages.Reset') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ratings Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.All_Ratings') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.User') }}</th>
                            <th>{{ __('messages.Driver') }}</th>
                            <th>{{ __('messages.Rating') }}</th>
                            <th>{{ __('messages.Review') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ratings as $rating)
                        <tr>
                            <td>{{ $rating->id }}</td>
                            <td>{{ $rating->user ? $rating->user->name : __('messages.Not_Available') }}</td>
                            <td>{{ $rating->driver ? $rating->driver->name : __('messages.Not_Available') }}</td>
                            <td>
                                <span class="badge badge-{{ $rating->rating_badge }}">
                                    {{ $rating->rating }} <i class="fas fa-star"></i>
                                </span>
                                <br>
                                <small>{!! $rating->stars !!}</small>
                            </td>
                            <td>
                                @if($rating->review)
                                    {{ Str::limit($rating->review, 50) }}
                                @else
                                    <em class="text-muted">{{ __('messages.No_Review') }}</em>
                                @endif
                            </td>
                            <td>{{ $rating->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                              
                                <form action="{{ route('ratings.destroy', $rating) }}" 
                                      method="POST" 
                                      style="display: inline-block;"
                                      onsubmit="return confirm('{{ __('messages.Are_you_sure_delete_rating') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-danger" 
                                            title="{{ __('messages.Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('messages.No_Ratings_Found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $ratings->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection