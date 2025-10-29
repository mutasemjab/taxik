@extends('layouts.admin')
@section('title')
    {{ __('messages.Pages') }}
@endsection



@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title card_title_center">{{ __('messages.Pages') }}</h3>

        <a href="{{ route('pages.create') }}" class="btn btn-sm btn-success">{{ __('messages.New') }} {{ __('messages.Pages') }}</a>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <div class="clearfix"></div>

        <div class="col-md-12">
            @if(isset($pages) && !empty($pages) && count($pages) > 0)
                <table id="example2" class="table table-bordered table-hover">
                    <thead class="custom_thead">
                        <th>{{ __('messages.Type') }}</th>
                        <th>{{ __('messages.Title') }}</th>
                        <th>{{ __('messages.Content') }}</th>
                        <th></th>
                    </thead>
                    <tbody>
                        @foreach ($pages as $page)
                            <tr>
                                <td>
                                     @if ($page->type ==1 )
                                     about us
                                     @elseif  ($page->type ==2 )
                                     Terms and Conditions
                                     @elseif ($page->type ==3 )
                                     Privacy Policy
                                    @endif
                                </td>
                                <td>{{ $page->title }}</td>
                                <td>{{ $page->content }}</td>
                                <td>
                                    <a href="{{ route('pages.edit', [ 'id' => $page->id]) }}" class="btn btn-sm btn-primary">{{ __('messages.Edit') }}</a>
                                    <form action="{{ route('pages.destroy', ['id' => $page->id]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-danger">{{ __('messages.No_data') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('assets/admin/js/sliderss.js') }}"></script>
@endsection
