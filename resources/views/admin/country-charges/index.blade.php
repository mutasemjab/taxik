@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.Country Charges') }}</h3>
                    @can('countrCharge-add')
                        <a href="{{ route('country-charges.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('messages.Add Country Charge') }}
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('messages.Country Name') }}</th>
                                    <th>{{ __('messages.Charge Data Count') }}</th>
                                    <th>{{ __('messages.Created At') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($countryCharges as $countryCharge)
                                    <tr>
                                        <td>{{ $loop->iteration + ($countryCharges->currentPage() - 1) * $countryCharges->perPage() }}</td>
                                        <td>
                                            <strong>{{ $countryCharge->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $countryCharge->chargeData->count() }} {{ __('messages.Items') }}</span>
                                        </td>
                                        <td>{{ $countryCharge->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-secondary" 
                                                    data-toggle="modal" 
                                                    data-target="#viewModal{{ $countryCharge->id }}">
                                                <i class="fas fa-eye"></i> {{ __('messages.View Details') }}
                                            </button>
                                            
                                            @can('countrCharge-edit')
                                                <a href="{{ route('country-charges.edit', $countryCharge->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
                                                </a>
                                            @endcan
                                            
                                            @can('countrCharge-delete')
                                                <form action="{{ route('country-charges.destroy', $countryCharge->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this country charge?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> {{ __('messages.Delete') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            {{ __('messages.No country charges found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $countryCharges->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals Section - Outside the table -->
@foreach($countryCharges as $countryCharge)
    <div class="modal fade" id="viewModal{{ $countryCharge->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel{{ $countryCharge->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel{{ $countryCharge->id }}">{{ $countryCharge->name }} - {{ __('messages.Charge Data') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('messages.Name') }}</th>
                                    <th>{{ __('messages.Phone') }}</th>
                                    <th>{{ __('messages.Service_provider') }}</th>
                                    <th>{{ __('messages.Cliq Name') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($countryCharge->chargeData as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $data->name }}</td>
                                        <td>{{ $data->phone }}</td>
                                        <td>{{ $data->service_provider }}</td>
                                        <td>{{ $data->cliq_name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ __('messages.Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection