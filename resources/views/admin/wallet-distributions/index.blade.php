@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.wallet_distributions') }}</h3>
                    <div>
                        <!-- زر تفعيل/تعطيل النظام -->
                        <form action="{{ route('wallet-distributions.toggle-system') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="enabled" value="{{ $systemEnabled ? 0 : 1 }}">
                            <button type="submit" class="btn btn-{{ $systemEnabled ? 'warning' : 'success' }}">
                                <i class="fas fa-{{ $systemEnabled ? 'ban' : 'check' }}"></i>
                                {{ $systemEnabled ? __('messages.disable_system') : __('messages.enable_system') }}
                            </button>
                        </form>
                        
                        @can('distribution-add')
                        <a href="{{ route('wallet-distributions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('messages.add_distribution') }}
                        </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- حالة النظام -->
                    <div class="alert alert-{{ $systemEnabled ? 'success' : 'warning' }} mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>{{ __('messages.system_status') }}:</strong>
                        {{ $systemEnabled ? __('messages.system_enabled') : __('messages.system_disabled') }}
                    </div>


                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('messages.total_amount') }}</th>
                                    <th>{{ __('messages.number_of_orders') }}</th>
                                    <th>{{ __('messages.amount_per_order') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.created_at') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributions as $distribution)
                                <tr class="{{ $distribution->activate == 1 ? 'table-success' : '' }}">
                                    <td>{{ $distribution->id }}</td>
                                    <td>
                                        <strong>{{ number_format($distribution->total_amount, 2) }} JD</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $distribution->number_of_orders }} {{ __('messages.orders') }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ number_format($distribution->amount_per_order, 2) }} JD</strong>
                                        <small class="text-muted">/ {{ __('messages.per_order') }}</small>
                                    </td>
                                    <td>
                                        @if($distribution->activate == 1)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i> {{ __('messages.active') }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-times-circle"></i> {{ __('messages.inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $distribution->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @can('distribution-edit')
                                        <!-- تفعيل/تعطيل -->
                                        <form action="{{ route('wallet-distributions.toggle-activate', $distribution->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-{{ $distribution->activate == 1 ? 'warning' : 'success' }}" 
                                                    title="{{ $distribution->activate == 1 ? __('messages.deactivate') : __('messages.activate') }}">
                                                <i class="fas fa-{{ $distribution->activate == 1 ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>

                                        <!-- تعديل -->
                                        <a href="{{ route('wallet-distributions.edit', $distribution->id) }}" 
                                           class="btn btn-sm btn-info" title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan

                                        @can('distribution-delete')
                                        <!-- حذف -->
                                        <form action="{{ route('wallet-distributions.destroy', $distribution->id) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ __('messages.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        {{ __('messages.no_distributions_found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $distributions->links() }}
                    </div>
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.how_it_works') }}</h5>
                    <ul class="mb-0">
                        <li>{{ __('messages.distribution_explanation_1') }}</li>
                        <li>{{ __('messages.distribution_explanation_2') }}</li>
                        <li>{{ __('messages.distribution_explanation_3') }}</li>
                        <li>{{ __('messages.distribution_explanation_4') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection