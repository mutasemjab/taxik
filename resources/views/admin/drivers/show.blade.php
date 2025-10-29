@extends('layouts.admin')

@section('title', __('messages.View_Driver'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.View_Driver') }}</h1>
        <div>
            <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
            </a>
            <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- Driver Profile -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Profile') }}</h6>
                </div>
                <div class="card-body text-center">
                    @if($driver->photo)
                    <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}" alt="{{ $driver->name }}" class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                    <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @endif
                    <h4 class="font-weight-bold">{{ $driver->name }}</h4>
                    <p class="text-muted mb-1">{{ $driver->phone }}</p>
                    @if($driver->email)
                    <p class="text-muted mb-1">{{ $driver->email }}</p>
                    @endif
                    <div class="mt-3">
                        @if($driver->activate == 1)
                        <span class="badge badge-success px-3 py-2">{{ __('messages.Active') }}</span>
                        @else
                        <span class="badge badge-danger px-3 py-2">{{ __('messages.Inactive') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Car Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Car_Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        @if($driver->photo_of_car)
                        <img src="{{ asset('assets/admin/uploads/' . $driver->photo_of_car) }}" alt="Car Photo" class="img-fluid rounded mb-3" style="max-height: 150px;">
                        @else
                        <div class="bg-light rounded py-5 mb-3">
                            <i class="fas fa-car fa-3x text-gray-300"></i>
                            <p class="mt-2 text-gray-500">{{ __('messages.No_Car_Photo') }}</p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="40%">{{ __('messages.Car_Model') }}</th>
                                    <td>{{ $driver->model ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Production_Year') }}</th>
                                    <td>{{ $driver->production_year ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Color') }}</th>
                                    <td>{{ $driver->color ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Plate_Number') }}</th>
                                    <td>{{ $driver->plate_number ?? __('messages.Not_Available') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Driver Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Driver_Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('messages.ID') }}</th>
                                    <td>{{ $driver->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Name') }}</th>
                                    <td>{{ $driver->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Phone') }}</th>
                                    <td>{{ $driver->phone }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Email') }}</th>
                                    <td>{{ $driver->email ?? __('messages.Not_Available') }}</td>
                                </tr>
                                <tr>
                                   
                                      <th> {{ __('messages.Options') }}</th>
                                    <td>
                                       @foreach($driver->options as $option)
                                           <span class="badge badge-success m-1">
                                               {{ $option->name }} 
                                           </span>
                                       @endforeach                         
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Balance') }}</th>
                                    <td>{{ $driver->balance }}</td>
                                </tr>
                             
                                <tr>
                                    <th>{{ __('messages.Status') }}</th>
                                    <td>
                                        @if($driver->activate == 1)
                                        <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                        @else
                                        <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Created_At') }}</th>
                                    <td>{{ $driver->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Updated_At') }}</th>
                                    <td>{{ $driver->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Documents -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Documents') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    {{ __('messages.Driving_License_Front') }}
                                </div>
                                <div class="card-body text-center">
                                    @if($driver->driving_license_front)
                                    <img src="{{ asset('assets/admin/uploads/' . $driver->driving_license_front) }}" alt="Driving License Front" class="img-fluid rounded" style="max-height: 200px;">
                                    <a href="{{ asset('assets/admin/uploads/' . $driver->driving_license_front) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-eye"></i> {{ __('messages.View_Full_Size') }}
                                    </a>
                                    @else
                                    <div class="bg-light rounded py-5">
                                        <i class="fas fa-id-card fa-3x text-gray-300"></i>
                                        <p class="mt-2 text-gray-500">{{ __('messages.Not_Available') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    {{ __('messages.Driving_License_Back') }}
                                </div>
                                <div class="card-body text-center">
                                    @if($driver->driving_license_back)
                                    <img src="{{ asset('assets/admin/uploads/' . $driver->driving_license_back) }}" alt="Driving License Back" class="img-fluid rounded" style="max-height: 200px;">
                                    <a href="{{ asset('assets/admin/uploads/' . $driver->driving_license_back) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-eye"></i> {{ __('messages.View_Full_Size') }}
                                    </a>
                                    @else
                                    <div class="bg-light rounded py-5">
                                        <i class="fas fa-id-card fa-3x text-gray-300"></i>
                                        <p class="mt-2 text-gray-500">{{ __('messages.Not_Available') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    {{ __('messages.Car_License_Front') }}
                                </div>
                                <div class="card-body text-center">
                                    @if($driver->car_license_front)
                                    <img src="{{ asset('assets/admin/uploads/' . $driver->car_license_front) }}" alt="Car License Front" class="img-fluid rounded" style="max-height: 200px;">
                                    <a href="{{ asset('assets/admin/uploads/' . $driver->car_license_front) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-eye"></i> {{ __('messages.View_Full_Size') }}
                                    </a>
                                    @else
                                    <div class="bg-light rounded py-5">
                                        <i class="fas fa-file-alt fa-3x text-gray-300"></i>
                                        <p class="mt-2 text-gray-500">{{ __('messages.Not_Available') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    {{ __('messages.Car_License_Back') }}
                                </div>
                                <div class="card-body text-center">
                                    @if($driver->car_license_back)
                                    <img src="{{ asset('assets/admin/uploads/' . $driver->car_license_back) }}" alt="Car License Back" class="img-fluid rounded" style="max-height: 200px;">
                                    <a href="{{ asset('assets/admin/uploads/' . $driver->car_license_back) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-eye"></i> {{ __('messages.View_Full_Size') }}
                                    </a>
                                    @else
                                    <div class="bg-light rounded py-5">
                                        <i class="fas fa-file-alt fa-3x text-gray-300"></i>
                                        <p class="mt-2 text-gray-500">{{ __('messages.Not_Available') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection