@extends('errors.layout')

@section('title', 'Internal Server Error')

@section('content')
    <div class="error-code">500</div>

    <h1 class="error-title">Something Went Wrong</h1>

    <p class="error-description">
        BRO360 encountered an unexpected error while processing your request.
        Please try again later. If the problem persists, contact the system administrator.
    </p>
@endsection