@extends('layouts.admin')

@section('title')
    {{ __('messages.Settings') }}
@endsection

@section('contentheaderactive')
    {{ __('messages.Show') }}
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title card_title_center">
            {{ __('messages.Settings') }}
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-12 table-responsive">

                @can('setting-table')
                    @if(isset($data) && !empty($data) && count($data) > 0)
                        <table class="table" style="width:100%">
                            <thead class="custom_thead">
                                <tr>
                                    <td>{{ __('messages.Key') }}</td>
                                    <td>{{ __('messages.Value') }}</td>
                                    <td>{{ __('messages.Action') }}</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $info)
                                    <tr>
                                        {{-- key is fixed from DB --}}
                                        <td>{{ __('messages.' . $info->key) }}</td>
                                        <td>{{ $info->value }}</td>
                                        <td>
                                            @can('setting-edit')
                                                <a href="{{ route('settings.edit', $info->id) }}"
                                                   class="btn btn-sm btn-primary">
                                                    {{ __('messages.Edit') }}
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <br>
                        {{ $data->links() }}
                    @else
                        <div class="alert alert-danger">
                            {{ __('messages.No_data') }}
                        </div>
                    @endif
                @endcan

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('assets/admin/js/Settings.js') }}"></script>
@endsection
