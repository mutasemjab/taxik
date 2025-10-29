@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.card_details') }}</h4>
                    <div>
                        <a href="{{ route('cards.card-numbers', $card) }}" class="btn btn-info btn-sm">
                            {{ __('messages.view_numbers') }}
                        </a>
                        <a href="{{ route('cards.edit', $card) }}" class="btn btn-warning btn-sm">
                            {{ __('messages.edit') }}
                        </a>
                        <a href="{{ route('cards.index') }}" class="btn btn-secondary btn-sm">
                            {{ __('messages.back_to_list') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th width="40%">{{ __('messages.id') }}</th>
                                        <td>{{ $card->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.pos') }}</th>
                                        <td>
                                            @if($card->pos)
                                                <span class="badge bg-info">{{ $card->pos->name }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('messages.no_pos') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.name') }}</th>
                                        <td>{{ $card->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.price') }}</th>
                                        <td><strong>{{ number_format($card->price, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.number_of_cards') }}</th>
                                        <td><span class="badge bg-primary">{{ number_format($card->number_of_cards) }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.created_at') }}</th>
                                        <td>{{ $card->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.updated_at') }}</th>
                                        <td>{{ $card->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">{{ __('messages.card_numbers_statistics') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="border rounded p-2">
                                                <h5 class="text-success mb-1">{{ $card->cardNumbers->count() }}</h5>
                                                <small>{{ __('messages.total_generated') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="border rounded p-2">
                                                <h5 class="text-primary mb-1">{{ $card->active_card_numbers_count }}</h5>
                                                <small>{{ __('messages.active_numbers') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <h5 class="text-danger mb-1">{{ $card->used_card_numbers_count }}</h5>
                                                <small>{{ __('messages.used_numbers') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <h5 class="text-info mb-1">{{ $card->unused_card_numbers_count }}</h5>
                                                <small>{{ __('messages.unused_numbers') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>{{ __('messages.recent_card_numbers') }}</h5>
                        @if($card->cardNumbers->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('messages.number') }}</th>
                                            <th>{{ __('messages.status') }}</th>
                                            <th>{{ __('messages.activate_status') }}</th>
                                            <th>{{ __('messages.created_at') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($card->cardNumbers->take(10) as $cardNumber)
                                            <tr>
                                                <td><code>{{ $cardNumber->formatted_number }}</code></td>
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
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($card->cardNumbers->count() > 10)
                                <div class="text-center mt-2">
                                    <a href="{{ route('cards.card-numbers', $card) }}" class="btn btn-outline-primary">
                                        {{ __('messages.view_all_numbers') }} ({{ $card->cardNumbers->count() }})
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                {{ __('messages.no_card_numbers_generated') }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <form action="{{ route('cards.regenerate-numbers', $card) }}" 
                                  method="POST" 
                                  style="display: inline-block;"
                                  onsubmit="return confirm('{{ __('messages.confirm_regenerate') }}')">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-success me-md-2">
                                    {{ __('messages.regenerate_numbers') }}
                                </button>
                            </form>
                            <a href="{{ route('cards.edit', $card) }}" class="btn btn-warning me-md-2">
                                {{ __('messages.edit') }}
                            </a>
                            <form action="{{ route('cards.destroy', $card) }}" 
                                  method="POST" 
                                  style="display: inline-block;"
                                  onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    {{ __('messages.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection