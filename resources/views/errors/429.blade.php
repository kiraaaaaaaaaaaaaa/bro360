@extends('errors.layout')

@section('title', 'Too Many Requests')

@section('content')
    <div class="error-code">429</div>

    <h1 class="error-title">Too Many Requests</h1>

    <p class="error-description">
        You have made too many requests within a short period.
        Please wait a moment before trying again.
    </p>
@endsection