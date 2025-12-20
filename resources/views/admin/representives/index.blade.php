@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.Representatives') }}</h3>
                    @can('representive-add')
                        <a href="{{ route('representives.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('messages.Add Representative') }}
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                  

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('messages.Name') }}</th>
                                    <th>{{ __('messages.Phone') }}</th>
                                    <th>{{ __('messages.Commission') }} (%)</th>
                                    <th>{{ __('messages.Created At') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($representives as $representive)
                                    <tr>
                                        <td>{{ $loop->iteration + ($representives->currentPage() - 1) * $representives->perPage() }}</td>
                                        <td>{{ $representive->name }}</td>
                                        <td>{{ $representive->phone }}</td>
                                        <td>{{ number_format($representive->commission, 2) }}%</td>
                                        <td>{{ $representive->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @can('representive-edit')
                                                <a href="{{ route('representives.edit', $representive->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
                                                </a>
                                            @endcan
                                            
                                            @can('representive-delete')
                                                <form action="{{ route('representives.destroy', $representive->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this representative?') }}')">
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
                                        <td colspan="6" class="text-center">
                                            {{ __('messages.No representatives found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $representives->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection