@extends('layouts.admin')
@section('title')
    {{ __('messages.Pages') }}
@endsection


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center"> {{ __('messages.Add_New') }} {{ __('messages.Pages') }} </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">


            <form action="{{ route('pages.store') }}" method="post" enctype='multipart/form-data'>
                <div class="row">
                    @csrf

                    <div class="col-md-6">
                        <div class="form-group">
                            <label> {{ __('messages.Title') }}</label>
                            <input name="title" id="title" class="form-control" value="{{ old('title') }}">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label> {{ __('messages.Content') }}</label>
                            <textarea name="content" id="content" class="form-control" rows="12"></textarea>
                            @error('content')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                          <label>  {{ __('messages.Type') }}</label>
                          <select name="type" id="type" class="form-control">
                           <option value=""> select</option>
                          <option   @if(old('type')==1  || old('type')=="" ) selected="selected"  @endif value="1"> About Us</option>
                           <option @if( (old('type')==2 and old('type')!="")) selected="selected"  @endif   value="2"> Terms & Condition</option>
                           <option @if( (old('type')==3 and old('type')!="")) selected="selected"  @endif   value="3"> Privacy & Policy</option>
                           <option @if( (old('type')==4 and old('type')!="")) selected="selected"  @endif   value="4"> Help</option>
                          </select>
                          @error('type')
                          <span class="text-danger">{{ $message }}</span>
                          @enderror
                          </div>
                        </div>



                    <div class="col-md-12">
                        <div class="form-group text-center">
                            <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm"> {{__('messages.Submit')}}</button>
                            <a href="{{ route('pages.index') }}" class="btn btn-sm btn-danger">{{__('messages.Cancel')}}</a>

                        </div>
                    </div>

                </div>
            </form>



        </div>




    </div>
    </div>
@endsection


@section('script')

@endsection
