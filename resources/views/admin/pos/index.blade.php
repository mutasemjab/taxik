@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.pos_list') }}</h4>
                    <a href="{{ route('pos.create') }}" class="btn btn-primary">
                        {{ __('messages.create_pos') }}
                    </a>
                </div>

                <div class="card-body">
                  
                    @if($posRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('messages.id') }}</th>
                                        <th>{{ __('messages.name') }}</th>
                                        <th>{{ __('messages.phone') }}</th>
                                        <th>{{ __('messages.address') }}</th>
                                        <th>{{ __('messages.created_at') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($posRecords as $pos)
                                        <tr>
                                            <td>{{ $pos->id }}</td>
                                            <td>{{ $pos->name }}</td>
                                            <td>{{ $pos->phone }}</td>
                                            <td>{{ Str::limit($pos->address, 50) }}</td>
                                            <td>{{ $pos->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                   
                                                    <a href="{{ route('pos.edit', $pos) }}" 
                                                       class="btn btn-warning btn-sm">
                                                        {{ __('messages.edit') }}
                                                    </a>
                                                   
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $posRecords->links() }}
                        </div>
                    @else
                        <div class="text-center">
                            <p class="text-muted">{{ __('messages.no_pos_found') }}</p>
                            <a href="{{ route('pos.create') }}" class="btn btn-primary">
                                {{ __('messages.create_first_pos') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection