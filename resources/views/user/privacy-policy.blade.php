@extends('layouts.user')

@section('content')
<div class="privacy-policy">
    <div class="container">
        <h1>Privacy Policy</h1>
        
        <!-- Optional: Last updated section -->
        @if(isset($page->updated_at))
        <div class="last-updated">
            <p>Last updated: {{ $page->updated_at->format('F j, Y') }}</p>
        </div>
        @endif
        
        <div class="privacy-content">
            {!! $page->content ?? '<p>Privacy policy content will be displayed here.</p>' !!}
        </div>
    </div>
    


</div>


@endsection