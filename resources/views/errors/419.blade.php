@extends('errors.layout')

@section('title', 'Session Expired')

@section('content')
    <div class="error-code">419</div>

    <h1 class="error-title">Session Expired</h1>

    <p class="error-description">
        Your session has expired or the page was inactive for too long.
        Please refresh the page or log in again to continue.
    </p>
@endsection