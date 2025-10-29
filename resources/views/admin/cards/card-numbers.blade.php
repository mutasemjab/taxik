@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.card_numbers_for') }}: {{ $card->name }}</h4>
                    <div>
                        <a href="{{ route('cards.show', $card) }}" class="btn btn-info btn-sm">
                            {{ __('messages.card_details') }}
                        </a>
                        <a href="{{ route('cards.index') }}" class="btn btn-secondary btn-sm">
                            {{ __('messages.back_to_cards') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">

                    <!-- Card Info Summary -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-2">
                                            <h6>{{ __('messages.card_name') }}</h6>
                                            <strong>{{ $card->name }}</strong>
                                        </div>
                                        <div class="col-md-2">
                                            <h6>{{ __('messages.price') }}</h6>
                                            <span class="badge bg-success">{{ number_format($card->price, 2) }}</span>
                                        </div>
                                        <div class="col-md-2">
                                            <h6>{{ __('messages.total_numbers') }}</h6>
                                            <span class="badge bg-primary">{{ $cardNumbers->total() }}</span>
                                        </div>
                                        <div class="col-md-2">
                                            <h6>{{ __('messages.active_numbers') }}</h6>
                                            <span class="badge bg-success">{{ $card->active_card_numbers_count }}</span>
                                        </div>
                                        <div class="col-md-2">
                                            <h6>{{ __('messages.used_numbers') }}</h6>
                                            <span class="badge bg-danger">{{ $card->used_card_numbers_count }}</span>
                                        </div>
                                        <div class="col-md-2">
                                            <h6>{{ __('messages.unused_numbers') }}</h6>
                                            <span class="badge bg-info">{{ $card->unused_card_numbers_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter and Actions -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <form method="GET" action="{{ route('cards.card-numbers', $card) }}" class="d-flex">
                                <select name="status" class="form-select me-2" onchange="this.form.submit()">
                                    <option value="">{{ __('messages.all_status') }}</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('messages.used') }}</option>
                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>{{ __('messages.not_used') }}</option>
                                </select>
                                <select name="activate" class="form-select me-2" onchange="this.form.submit()">
                                    <option value="">{{ __('messages.all_activate') }}</option>
                                    <option value="1" {{ request('activate') == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                    <option value="2" {{ request('activate') == '2' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                </select>
                                <button type="submit" class="btn btn-outline-primary">{{ __('messages.filter') }}</button>
                            </form>
                        </div>
                        <div class="col-md-4 text-end">
                            <form action="{{ route('cards.regenerate-numbers', $card) }}" 
                                  method="POST" 
                                  style="display: inline-block;"
                                  onsubmit="return confirm('{{ __('messages.confirm_regenerate') }}')">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-warning">
                                    {{ __('messages.regenerate_all') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($cardNumbers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('messages.id') }}</th>
                                        <th>{{ __('messages.card_number') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.activate_status') }}</th>
                                        <th>{{ __('messages.created_at') }}</th>
                                        <th>{{ __('messages.updated_at') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cardNumbers as $cardNumber)
                                        <tr>
                                            <td>{{ $cardNumber->id }}</td>
                                            <td>
                                               {{ $cardNumber->number }}
                                            </td>
                                            <td>
                                                @if($cardNumber->status == 1)
                                                    <span class="badge bg-danger">{{ __('messages.used') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ __('messages.not_used') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($cardNumber->activate == 1)
                                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                                @else
                                                    <span class="badge bg-warning">{{ __('messages.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $cardNumber->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $cardNumber->updated_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <form action="{{ route('card-numbers.toggle-status', $cardNumber) }}" 
                                                          method="POST" 
                                                          style="display: inline-block;">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if($cardNumber->status == 1)
                                                            <button type="submit" class="btn btn-success btn-sm" 
                                                                    title="{{ __('messages.mark_as_not_used') }}">
                                                                {{ __('messages.mark_unused') }}
                                                            </button>
                                                        @else
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                    title="{{ __('messages.mark_as_used') }}">
                                                                {{ __('messages.mark_used') }}
                                                            </button>
                                                        @endif
                                                    </form>
                                                    
                                                    <form action="{{ route('card-numbers.toggle-activate', $cardNumber) }}" 
                                                          method="POST" 
                                                          style="display: inline-block;">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if($cardNumber->activate == 1)
                                                            <button type="submit" class="btn btn-warning btn-sm"
                                                                    title="{{ __('messages.deactivate') }}">
                                                                {{ __('messages.deactivate') }}
                                                            </button>
                                                        @else
                                                            <button type="submit" class="btn btn-primary btn-sm"
                                                                    title="{{ __('messages.activate') }}">
                                                                {{ __('messages.activate') }}
                                                            </button>
                                                        @endif
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $cardNumbers->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center">
                            <p class="text-muted">{{ __('messages.no_card_numbers_found') }}</p>
                            <form action="{{ route('cards.regenerate-numbers', $card) }}" 
                                  method="POST" 
                                  style="display: inline-block;"
                                  onsubmit="return confirm('{{ __('messages.confirm_regenerate') }}')">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-primary">
                                    {{ __('messages.generate_numbers') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection