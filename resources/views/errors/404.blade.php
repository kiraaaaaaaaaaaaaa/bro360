@extends('errors.layout')

@section('title', 'Page Not Found')

@section('content')
    <div class="error-code">404</div>

    <h1 class="error-title">Page Not Found</h1>

    <p class="error-description">
        The page you are looking for may have been moved, deleted,
        or the address may be incorrect.
        Please check the URL or return to your dashboard.
    </p>
@endsection