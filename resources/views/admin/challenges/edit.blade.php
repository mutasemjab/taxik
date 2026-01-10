@extends('layouts.admin')

@section('title', __('messages.Edit_Challenge'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Edit_Challenge') }}</h1>
        <a href="{{ route('challenges.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back') }}
        </a>
    </div>

    <!-- Challenge Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Challenge_Information') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('challenges.update', $challenge->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Current Icon Preview -->
                @if($challenge->icon)
                <div class="form-group text-center">
                    <label>{{ __('messages.Current_Icon') }}</label><br>
                    <img src="{{ asset('assets/admin/uploads/challenges/' . $challenge->icon) }}" 
                         alt="{{ $challenge->getTitle() }}" 
                         style="max-width: 150px; max-height: 150px;">
                </div>
                @endif

                <div class="row">
                    <!-- Title English -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title_en">{{ __('messages.Title_English') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title_en') is-invalid @enderror" 
                                   id="title_en" name="title_en" value="{{ old('title_en', $challenge->title_en) }}" required>
                            @error('title_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Title Arabic -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title_ar">{{ __('messages.Title_Arabic') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title_ar') is-invalid @enderror" 
                                   id="title_ar" name="title_ar" value="{{ old('title_ar', $challenge->title_ar) }}" required>
                            @error('title_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Description English -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="description_en">{{ __('messages.Description_English') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                      id="description_en" name="description_en" rows="3" required>{{ old('description_en', $challenge->description_en) }}</textarea>
                            @error('description_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description Arabic -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="description_ar">{{ __('messages.Description_Arabic') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                      id="description_ar" name="description_ar" rows="3" required>{{ old('description_ar', $challenge->description_ar) }}</textarea>
                            @error('description_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Challenge Type -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="challenge_type">{{ __('messages.Challenge_Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('challenge_type') is-invalid @enderror" 
                                    id="challenge_type" name="challenge_type" required>
                                <option value="">{{ __('messages.Select_Type') }}</option>
                                @foreach($challengeTypes as $key => $type)
                                <option value="{{ $key }}" {{ old('challenge_type', $challenge->challenge_type) == $key ? 'selected' : '' }}>
                                    {{ $type['en'] }} - {{ $type['ar'] }}
                                </option>
                                @endforeach
                            </select>
                            @error('challenge_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Target Count -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="target_count">{{ __('messages.Target_Count') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('target_count') is-invalid @enderror" 
                                   id="target_count" name="target_count" min="1" value="{{ old('target_count', $challenge->target_count) }}" required>
                            @error('target_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.target_count_help') }}</small>
                        </div>
                    </div>

                    <!-- Reward Amount -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="reward_amount">{{ __('messages.Reward_Amount') }} ({{ __('messages.currency') }}) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('reward_amount') is-invalid @enderror" 
                                   id="reward_amount" name="reward_amount" step="0.01" min="0" value="{{ old('reward_amount', $challenge->reward_amount) }}" required>
                            @error('reward_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Start Date -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="start_date">{{ __('messages.Start_Date') }}</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                   id="start_date" name="start_date" value="{{ old('start_date', $challenge->start_date ? $challenge->start_date->format('Y-m-d') : '') }}">
                            @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.start_date_help') }}</small>
                        </div>
                    </div>

                    <!-- End Date -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="end_date">{{ __('messages.End_Date') }}</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" value="{{ old('end_date', $challenge->end_date ? $challenge->end_date->format('Y-m-d') : '') }}">
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.end_date_help') }}</small>
                        </div>
                    </div>

                    <!-- Max Completions Per User -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="max_completions_per_user">{{ __('messages.Max_Completions_Per_User') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('max_completions_per_user') is-invalid @enderror" 
                                   id="max_completions_per_user" name="max_completions_per_user" min="1" value="{{ old('max_completions_per_user', $challenge->max_completions_per_user) }}" required>
                            @error('max_completions_per_user')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.max_completions_help') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Icon -->
                <div class="form-group">
                    <label for="icon">{{ __('messages.Challenge_Icon') }}</label>
                    <input type="file" class="form-control-file @error('icon') is-invalid @enderror" 
                           id="icon" name="icon" accept="image/*">
                    @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">{{ __('messages.icon_update_help') }}</small>
                </div>

                <!-- Is Active -->
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $challenge->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">{{ __('messages.Active') }}</label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('messages.Update_Challenge') }}
                    </button>
                    <a href="{{ route('challenges.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection