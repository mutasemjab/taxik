@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.cards_list') }}</h4>
                    <a href="{{ route('cards.create') }}" class="btn btn-primary">
                        {{ __('messages.create_card') }}
                    </a>
                </div>

                <div class="card-body">
               

                    @if($cards->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('messages.id') }}</th>
                                        <th>{{ __('messages.pos') }}</th>
                                        <th>{{ __('messages.name') }}</th>
                                        <th>{{ __('messages.price') }}</th>
                                        <th>{{ __('messages.number_of_cards') }}</th>
                                        <th>{{ __('messages.generated_numbers') }}</th>
                                        <th>{{ __('messages.active_inactive') }}</th>
                                        <th>{{ __('messages.used_unused') }}</th>
                                        <th>{{ __('messages.created_at') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cards as $card)
                                        <tr>
                                            <td>{{ $card->id }}</td>
                                            <td>
                                                @if($card->pos)
                                                    <span class="badge bg-info">{{ $card->pos->name }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('messages.no_pos') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $card->name }}</td>
                                            <td>{{ number_format($card->price, 2) }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ number_format($card->number_of_cards) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $card->cardNumbers->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $card->active_card_numbers_count }}</span> /
                                                <span class="badge bg-warning">{{ $card->cardNumbers->count() - $card->active_card_numbers_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">{{ $card->used_card_numbers_count }}</span> /
                                                <span class="badge bg-info">{{ $card->unused_card_numbers_count }}</span>
                                            </td>
                                            <td>{{ $card->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="btn-group-vertical" role="group">
                                                    <a href="{{ route('cards.show', $card) }}" 
                                                       class="btn btn-info btn-sm mb-1">
                                                        {{ __('messages.view') }}
                                                    </a>
                                                    <a href="{{ route('cards.card-numbers', $card) }}" 
                                                       class="btn btn-secondary btn-sm mb-1">
                                                        {{ __('messages.view_numbers') }}
                                                    </a>
                                                    <a href="{{ route('cards.edit', $card) }}" 
                                                       class="btn btn-warning btn-sm mb-1">
                                                        {{ __('messages.edit') }}
                                                    </a>
                                                    <form action="{{ route('cards.regenerate-numbers', $card) }}" 
                                                          method="POST" 
                                                          style="display: inline-block;"
                                                          onsubmit="return confirm('{{ __('messages.confirm_regenerate') }}')">
                                                        @csrf
                                                        @method('POST')
                                                        <button type="submit" class="btn btn-success btn-sm mb-1">
                                                            {{ __('messages.regenerate') }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('cards.destroy', $card) }}" 
                                                          method="POST" 
                                                          style="display: inline-block;"
                                                          onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            {{ __('messages.delete') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $cards->links() }}
                        </div>
                    @else
                        <div class="text-center">
                            <p class="text-muted">{{ __('messages.no_cards_found') }}</p>
                            <a href="{{ route('cards.create') }}" class="btn btn-primary">
                                {{ __('messages.create_first_card') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection