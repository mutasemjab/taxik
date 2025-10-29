@extends("layouts.admin")


@section('css')
@endsection

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">

                            <li class="breadcrumb-item"><a
                                    href="{{ route('admin.role.index') }}">Role</a>
                            </li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Create Role</h4>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.role.store') }}" method="post">
                            @csrf
                            <div class="my-3">
                                <input type="text"
                                    class="form-control @if ($errors->has('name')) is-invalid @endif" id="name"
                                    placeholder=" Name" value="{{ old('name') }}" name="name">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="emsg text-danger"></span>
                            </div>
                            <h1>Permission</h1>
                            <div class="my-3">
                                @foreach($data as $value)
                                    <br>
                                    <input {{in_array( $value->id,old('perms')? old('perms'): []) ? 'checked':''}} class="ml-5" type="checkbox" name="perms[]" id="perm_{{$value->id}}" value="{{ $value->id }}">
                                    <label for="perm_{{$value->id}}"> {{ $value->name }}. </label>
                                    <br>
                                @endforeach
                            </div>
                            <div class="row" id="permissions">
                                @error('perms')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="emsg text-danger"></span>
                            </div>




                            <div class="text-right">
                                <button type="submit"
                                    class="btn btn-success waves-effect waves-light">Save</button>
                                <a type="button" href="{{ route('admin.role.index') }}"
                                    class="btn btn-danger waves-effect waves-light m-l-10">Cancel
                                </a>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('#guard_name').change(function(e) {
            guard_name = $('#guard_name').val();
            e.preventDefault();
            $.ajax({
                type: "GET",
                url: "/admin" + '/permissions/' + guard_name,
                success: function(response) {
                    $('#permissions').empty();
                    $.each(response, function(i, val) {
                        $('#permissions').append(
                            '<div class="col-8"><input type="checkbox" class="mx-2" name="permissions[]" value=' +
                            val.id + '>' + val.name + '</div>');
                    });
                }
            });
        });
    </script>
@endsection
