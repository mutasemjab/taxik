@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.View_Complaint') }}</h1>
        <a href="{{ route('complaints.index') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> {{ __('messages.Back_to_List') }}
        </a>
    </div>

    <!-- Complaint Details -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Complaint_Details') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('messages.Subject') }}:</label>
                        <p>{{ $complaint->subject }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('messages.Status') }}:</label>
                        <p>
                            <span class="badge badge-{{ $complaint->status_badge }}">
                                {{ $complaint->status_label }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('messages.Description') }}:</label>
                        <p>{{ $complaint->description ?? __('messages.Not_Available') }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('messages.User') }}:</label>
                        <p>{{ $complaint->user ? $complaint->user->name : __('messages.Not_Available') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('messages.Driver') }}:</label>
                        <p>{{ $complaint->driver ? $complaint->driver->name : __('messages.Not_Available') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('messages.Order_ID') }}:</label>
                        <p>{{ $complaint->order ? $complaint->order->id : __('messages.Not_Available') }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('messages.Created_At') }}:</label>
                        <p>{{ $complaint->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('messages.Updated_At') }}:</label>
                        <p>{{ $complaint->updated_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h5 class="font-weight-bold">{{ __('messages.Update_Status') }}</h5>
                   <form action="{{ route('complaints.update-status', $complaint) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="status">{{ __('messages.Status') }}</label>
                            <select class="form-control" id="status" name="status">
                                <option value="1" {{ $complaint->status == 1 ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                <option value="2" {{ $complaint->status == 2 ? 'selected' : '' }}>{{ __('messages.In_Progress') }}</option>
                                <option value="3" {{ $complaint->status == 3 ? 'selected' : '' }}>{{ __('messages.Done') }}</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('messages.Update_Status') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
