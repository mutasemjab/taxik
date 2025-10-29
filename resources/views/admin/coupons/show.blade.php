@extends('layouts.admin')

@section('title', __('messages.View_Coupon'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.View_Coupon') }}</h1>
        <div>
            <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
            </a>
            <a href="{{ route('coupons.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <!-- Coupon Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">{{ $coupon->title }}</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-light p-4 rounded">
                            <h2 class="text-primary font-weight-bold">{{ $coupon->code }}</h2>
                            <span class="badge badge-{{ $coupon->getStatusClass() }} px-3 py-2 mt-2">
                                {{ $coupon->getStatus() }}
                            </span>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-md-6 mb-3">
                            <h5>{{ __('messages.Discount') }}</h5>
                            <h4 class="text-primary font-weight-bold">{{ $coupon->getFormattedDiscount() }}</h4>
                            <span class="badge badge-info">{{ $coupon->getDiscountTypeText() }}</span>
                        </div>
                       
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6>{{ __('messages.Coupon_Type') }}</h6>
                        <span class="badge badge-primary px-3 py-2">{{ $coupon->getCouponTypeText() }}</span>
                        @if($coupon->coupon_type == 3 && $coupon->service)
                            <div class="mt-2">
                                <small class="text-muted">{{ __('messages.Service') }}:</small>
                                <div>{{ $coupon->service->name_en }}</div>
                                <div>{{ $coupon->service->name_ar }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6>{{ __('messages.Start_Date') }}</h6>
                            <p>{{ $coupon->start_date->format('Y-m-d') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6>{{ __('messages.End_Date') }}</h6>
                            <p>{{ $coupon->end_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <!-- Coupon Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Coupon_Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('messages.ID') }}</th>
                                    <td>{{ $coupon->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Coupon_Code') }}</th>
                                    <td>{{ $coupon->code }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Title') }}</th>
                                    <td>{{ $coupon->title }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Current_Status') }}</th>
                                    <td>
                                        <span class="badge badge-{{ $coupon->getStatusClass() }}">
                                            {{ $coupon->getStatus() }}
                                        </span>
                                        <small class="text-muted ml-2">
                                            ({{ $coupon->activate == 1 ? __('messages.Enabled') : __('messages.Disabled') }})
                                        </small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Created_At') }}</th>
                                    <td>{{ $coupon->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Updated_At') }}</th>
                                    <td>{{ $coupon->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
         
        </div>
    </div>
</div>
@endsection