
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'System Error') | BRO360</title>

    <link rel="stylesheet" href="{{ asset('css/errors.css') }}">
</head>
<body>

    <div class="error-page">

        <div class="error-card">

            <div class="error-brand">
                <img src="{{ asset('images\mfbm.png') }}" alt="BRO360 Logo">
                <span>BRO360</span>
            </div>

            <div class="error-content">
                @yield('content')
            </div>

            <div class="error-actions">
                <a href="{{ url('/dashboard') }}" class="error-btn-primary">
                    Back to Dashboard
                </a>

                <button type="button" onclick="history.back()" class="error-btn-secondary">
                    Go Back
                </button>
            </div>

            <div class="error-footer">
                2026 © BRO | Bangsamoro Revenue Office
            </div>

        </div>

    </div>

</body>
</html>
```