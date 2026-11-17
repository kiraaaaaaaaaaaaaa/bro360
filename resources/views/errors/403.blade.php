@extends('errors.layout')

@section('title', 'Access Denied')

@section('content')
    <div class="error-code">403</div>

    <h1 class="error-title">Access Denied</h1>

    <p class="error-description">
        You do not have the necessary permissions to access this page.
        If you believe this is an error, please contact your system administrator.
    </p>
@endsection