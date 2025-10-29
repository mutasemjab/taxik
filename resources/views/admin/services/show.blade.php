@extends('layouts.admin')

@section('title', __('messages.View_Service'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.View_Service') }}</h1>
        <div>
            <a href="{{ route('services.edit', $service->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
            </a>
            <a href="{{ route('services.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- Service Image -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Service_Image') }}</h6>
                </div>
                <div class="card-body text-center">
                    @if($service->photo)
                    <img src="{{ asset('assets/admin/uploads/' . $service->photo) }}" alt="{{ $service->getName() }}" class="img-fluid rounded mb-3" style="max-height: 250px;">
                    @else
                    <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No Image" class="img-fluid rounded mb-3" style="max-height: 250px;">
                    @endif
                    <h4 class="font-weight-bold">{{ $service->name_en }}</h4>
                    <p class="text-muted mb-1">{{ $service->name_ar }}</p>
                    <div class="mt-2">
                        <span class="badge badge-{{ $service->activate == 1 ? 'success' : 'danger' }} badge-lg">
                            {{ $service->activate == 1 ? __('messages.Active') : __('messages.Inactive') }}
                        </span>
                        @if($service->is_electric == 1)
                        <span class="badge badge-success badge-lg">
                            <i class="fas fa-bolt"></i> {{ __('messages.Electric') }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Payment_Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('messages.Payment_Methods') }}</label>
                        <div>
                            @foreach($service->servicePayments as $payment)
                                <span class="badge badge-success m-1">
                                    {{ $payment->payment_method_text }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-sm">
                            <tbody>
                                <tr>
                                    <th width="60%">{{ __('messages.Admin_Commission') }}</th>
                                    <td>
                                        {{ $service->admin_commision }}
                                        <span class="badge badge-info">{{ $service->getCommisionTypeText() }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Cancellation_Fee') }}</th>
                                    <td>{{ $service->cancellation_fee }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Waiting_Time') }}</th>
                                    <td>{{ $service->waiting_time }} {{ __('messages.minutes') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Capacity') }}</th>
                                    <td><i class="fas fa-users"></i> {{ $service->capacity }} {{ __('messages.passengers') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Service Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Service_Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('messages.ID') }}</th>
                                    <td>{{ $service->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Name_English') }}</th>
                                    <td>{{ $service->name_en }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Name_Arabic') }}</th>
                                    <td>{{ $service->name_ar }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Type') }}</th>
                                    <td>
                                        @if($service->is_electric == 1)
                                            <span class="badge badge-success"><i class="fas fa-bolt"></i> {{ __('messages.Electric') }}</span>
                                        @else
                                            <span class="badge badge-secondary"><i class="fas fa-gas-pump"></i> {{ __('messages.Fuel') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Created_At') }}</th>
                                    <td>{{ $service->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Morning Pricing -->
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-sun"></i> {{ __('messages.Morning_Pricing') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-warning text-white mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">{{ __('messages.Start_Price') }}</h6>
                                    <h2>{{ $service->start_price_morning }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">{{ __('messages.Price_Per_KM') }}</h6>
                                    <h2>{{ $service->price_per_km_morning }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">{{ __('messages.Price_Per_Minute') }}</h6>
                                    <h2>{{ $service->price_of_real_one_minute_morning }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <i class="fas fa-calculator"></i> {{ __('messages.Example_Trip_Cost') }}
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>{{ __('messages.For_5_KM_Trip') }}</strong></p>
                                    <h4 class="text-warning">{{ $service->start_price_morning + ($service->price_per_km_morning * 5) }}</h4>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>{{ __('messages.For_10_KM_Trip') }}</strong></p>
                                    <h4 class="text-warning">{{ $service->start_price_morning + ($service->price_per_km_morning * 10) }}</h4>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>{{ __('messages.For_15_KM_Trip') }}</strong></p>
                                    <h4 class="text-warning">{{ $service->start_price_morning + ($service->price_per_km_morning * 15) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evening Pricing -->
            <div class="card shadow mb-4 border-left-info">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-moon"></i> {{ __('messages.Evening_Pricing') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">{{ __('messages.Start_Price') }}</h6>
                                    <h2>{{ $service->start_price_evening }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">{{ __('messages.Price_Per_KM') }}</h6>
                                    <h2>{{ $service->price_per_km_evening }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">{{ __('messages.Price_Per_Minute') }}</h6>
                                    <h2>{{ $service->price_of_real_one_minute_evening }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <i class="fas fa-calculator"></i> {{ __('messages.Example_Trip_Cost') }}
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>{{ __('messages.For_5_KM_Trip') }}</strong></p>
                                    <h4 class="text-info">{{ $service->start_price_evening + ($service->price_per_km_evening * 5) }}</h4>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>{{ __('messages.For_10_KM_Trip') }}</strong></p>
                                    <h4 class="text-info">{{ $service->start_price_evening + ($service->price_per_km_evening * 10) }}</h4>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>{{ __('messages.For_15_KM_Trip') }}</strong></p>
                                    <h4 class="text-info">{{ $service->start_price_evening + ($service->price_per_km_evening * 15) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commission Example -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-percentage"></i> {{ __('messages.Admin_Fee_Example') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            @if($service->type_of_commision == 1)
                            <p class="mb-2">{{ __('messages.Fixed_Amount_Per_Trip') }}</p>
                            <h3 class="text-success">{{ $service->admin_commision }}</h3>
                            @else
                            <p class="mb-2">{{ __('messages.Commission_Percentage') }}</p>
                            <h3 class="text-success">{{ $service->admin_commision }}%</h3>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">{{ __('messages.For_100_Trip_Cost') }}</p>
                            @if($service->type_of_commision == 1)
                            <h3 class="text-success">{{ $service->admin_commision }}</h3>
                            @else
                            <h3 class="text-success">{{ ($service->admin_commision / 100) * 100 }}</h3>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection