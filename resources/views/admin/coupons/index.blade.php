@extends('layouts.admin')

@section('title', __('messages.Coupons'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Coupons') }}</h1>
        <a href="{{ route('coupons.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_Coupon') }}
        </a>
    </div>


    <!-- Coupons Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Coupons_List') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Code') }}</th>
                            <th>{{ __('messages.Title') }}</th>
                            <th>{{ __('messages.Discount') }}</th>
                            <th>{{ __('messages.Validity') }}</th>
                            <th>{{ __('messages.Coupon_Type') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td>
                                <span class="font-weight-bold">{{ $coupon->code }}</span>
                            </td>
                            <td>{{ $coupon->title }}</td>
                            <td>
                                {{ $coupon->getFormattedDiscount() }}
                                <span class="badge badge-info">{{ $coupon->getDiscountTypeText() }}</span>
                            </td>
                            <td>
                                <small>{{ __('messages.Start') }}: {{ $coupon->start_date->format('Y-m-d') }}</small><br>
                                <small>{{ __('messages.End') }}: {{ $coupon->end_date->format('Y-m-d') }}</small>
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $coupon->getCouponTypeText() }}</span>
                                @if($coupon->coupon_type == 3 && $coupon->service)
                                <small class="d-block text-muted mt-1">{{ $coupon->service->name_en }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $coupon->getStatusClass() }}">{{ $coupon->getStatus() }}</span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('coupons.show', $coupon->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                  
                                    <a href="#" class="btn btn-danger btn-sm" onclick="event.preventDefault(); if(confirm('{{ __('messages.Delete_Confirm') }}')) document.getElementById('delete-form-{{ $coupon->id }}').submit();">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <form id="delete-form-{{ $coupon->id }}" action="{{ route('coupons.destroy', $coupon->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
@endsection