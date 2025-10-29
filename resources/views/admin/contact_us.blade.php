@extends('layouts.admin')
@section('title')
    Contact Us
@endsection


@section('contentheaderactive')
    show
@endsection



@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center"> Contact US </h3>


        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                </div>
            </div>
            <div class="clearfix"></div>


            @if (@isset($data) && !@empty($data) && count($data) > 0)
                <table id="example2" class="table table-bordered table-hover">
                    <thead class="custom_thead">

                        <th>name </th>
                        <th> email </th>
                        <th> mobile </th>
                        <th>subject</th>
                        <th>message</th>

                    </thead>
                    <tbody>
                        @foreach ($data as $info)
                            <tr>

                                <td>{{ $info->first_name . ' ' . $info->last_name }}</td>
                                <td>{{ $info->email }}</td>
                                <td>{{ $info->phone }}</td>

                                <td>{{ $info->subject }}</td>
                                <td>{{ $info->message }}</td>

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

    </div>

    </div>

    </div>

@endsection

@section('script')
    <script src="{{ asset('assets/admin/js/Products.js') }}"></script>
@endsection
