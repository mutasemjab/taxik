@extends('layouts.admin')
@section('title')
    edit Setting
@endsection



@section('contentheaderlink')
    <a href="{{ route('settings.index') }}"> Setting </a>
@endsection

@section('contentheaderactive')
    تعديل
@endsection


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center"> edit Setting </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">


            <form action="{{ route('settings.update', $data['id']) }}" method="post" enctype='multipart/form-data'>
                <div class="row">
                    @csrf
                    @method('PUT')




                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.key') }}</label>
                            <input name="key" id="key" class="form-control" value="{{ old('key', $data['key']) }}"
                                readonly>
                            @error('key')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.value') }}</label>
                            <input name="value" id="value" class="form-control"
                                value="{{ old('value', $data['value']) }}">
                            @error('value')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>



                    <div class="col-md-12">
                        <div class="form-group text-center">
                            <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm"> update</button>
                            <a href="{{ route('settings.index') }}" class="btn btn-sm btn-danger">cancel</a>

                        </div>
                    </div>

                </div>
            </form>



        </div>




    </div>
    </div>
@endsection
