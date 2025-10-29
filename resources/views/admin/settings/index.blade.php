@extends('layouts.admin')
@section('title')
    Setting
@endsection


@section('contentheaderactive')
    show
@endsection



@section('content')



    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center"> Setting </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 table-responsive">


                    @can('setting-table')
                        @if (@isset($data) && !@empty($data) && count($data) > 0)
                            <table style="width:100%" id="" class="table">
                                <thead class="custom_thead">
                                    <td>{{ __('messages.key') }}</td>
                                    <td>{{ __('messages.value') }}</td>
                                    <td>{{ __('messages.Action') }}</td>

                                </thead>
                                <tbody>
                                    @foreach ($data as $info)
                                        <tr>



                                            <td>{{ $info->key }}</td>
                                            <td>{{ $info->value }}</td>


                                            <td>
                                                @can('setting-edit')
                                                    <a href="{{ route('settings.edit', $info->id) }}"
                                                        class="btn btn-sm  btn-primary">edit</a>
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
                                there is no data found !! </div>
                        @endif

                    </div>
                @endcan



            </div>

        </div>

    </div>

@endsection

@section('script')
    <script src="{{ asset('assets/admin/js/Settings.js') }}"></script>
@endsection
