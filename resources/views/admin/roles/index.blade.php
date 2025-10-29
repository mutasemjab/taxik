@extends("layouts.admin")

@section('css')
@endsection

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{ env('APP_NAME') }}</a></li>
                            <li class="breadcrumb-item active">Role</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Role</h4>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-2">
                                <div class="col-md-12">

                                </div>
                            <div class="col-sm-4">

                                {{ $data->links() }}

                            </div>
                            <div class="col-sm-8">
                                <div class="text-sm-right">
                                    <a type="button" href="{{ route("admin.role.create") }}"
                                        class="btn btn-primary waves-effect waves-light mb-2 text-white">New Role
                                    </a>
                                </div>
                            </div><!-- end col-->
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-hover mb-0">
                                <thead class="thead-light">

                                    <tr>
                                         <th>Name</th>
                                        <th>Permissions</th>
                                         <th style="width: 82px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $value)
                                        <tr>
                                            <td><span class="font-weight-bold">{{ $value->name }}</span></td>
                                            <td>
                                                @foreach ($value->permissions as $permission)
                                                    {{ $permission->name }}<br>
                                                @endforeach
                                            </td>
                                             <td>
                                                <a class="btn btn-sm btn-outline-info"
                                                    href="{{ route("admin.role.edit",  $value->id) }}"><i
                                                        class="mdi mdi-pencil-box"></i> Edit</a>
                                              <a class="btn btn-sm btn-outline-danger" href="javascript:void(0)"
                                                   @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="Delete('{{ $value->id }}','{{ route('admin.role.delete') }}')" @endif><i
                                                        class="mdi mdi-trash-can"></i>Delete</a>

                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>

                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div>
        </div>
    </div> <!-- container -->
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.3/dist/sweetalert2.min.js"></script>
    <script src="{{ asset('assets/js/category.js') }}"></script>
@endsection
