
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | BRO360</title>
    <link rel="icon" type="image/png" href="{{ asset('images/mfbm.png') }}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f5f7f5;
            color: #1c2922;
        }

        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .brand-panel {
            width: 52%;
            background: linear-gradient(145deg, #123d2c, #09291e);
            color: white;
            padding: 65px 8%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            width: 480px;
            height: 480px;
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 50%;
            right: -180px;
            bottom: -170px;
            box-shadow: 0 0 0 55px rgba(255,255,255,.025),
                        0 0 0 110px rgba(255,255,255,.02);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            border: 2px solid #d8b65c;
            color: #e7c66c;
            display: grid;
            place-items: center;
            font-size: 22px;
            font-weight: 800;
            border-radius: 12px;
        }

        .brand-name {
            font-size: 25px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .brand-subtitle {
            font-size: 10px;
            letter-spacing: 2px;
            color: #c8d8ce;
            margin-top: 4px;
        }

        .brand-content {
            max-width: 520px;
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            color: #e3c36c;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 22px;
        }

        .brand-content h1 {
            font-size: clamp(36px, 4vw, 58px);
            line-height: 1.12;
            margin-bottom: 22px;
        }

        .brand-content h1 span {
            color: #e3c36c;
        }

        .brand-content p {
            color: #d0ddd5;
            font-size: 16px;
            line-height: 1.8;
            max-width: 450px;
        }

        .brand-footer {
            color: #b8c9be;
            font-size: 12px;
            position: relative;
            z-index: 1;
        }

        .form-panel {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 30px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
        }

        .mobile-brand {
            display: none;
        }

        .login-card h2 {
            font-size: 30px;
            margin-bottom: 10px;
            color: #173b2b;
        }

        .description {
            color: #78857d;
            font-size: 14px;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 21px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 650;
            margin-bottom: 9px;
            color: #35483d;
        }

        .input-wrap {
            position: relative;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            height: 52px;
            padding: 0 15px;
            border: 1px solid #dce4de;
            border-radius: 9px;
            outline: none;
            font-size: 14px;
            background: white;
            transition: .2s;
        }

        input:focus {
            border-color: #246443;
            box-shadow: 0 0 0 3px rgba(36,100,67,.10);
        }

        .password-input {
            padding-right: 75px !important;
        }

        .toggle-password {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: none;
            color: #52715f;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 5px 0 27px;
            font-size: 13px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #627168;
        }

        .remember input {
            accent-color: #1e593b;
        }

        .login-button {
            width: 100%;
            height: 52px;
            background: #174b32;
            border: none;
            border-radius: 9px;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
        }

        .login-button:hover {
            background: #103c28;
            transform: translateY(-1px);
        }

        .error-message {
            background: #fff0ef;
            border: 1px solid #f3ceca;
            color: #a93226;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .field-error {
            color: #bd3930;
            font-size: 12px;
            margin-top: 7px;
        }

        .security-note {
            margin-top: 30px;
            text-align: center;
            color: #89958d;
            font-size: 12px;
            line-height: 1.6;
        }

        @media (max-width: 850px) {
            .brand-panel {
                display: none;
            }

            .form-panel {
                background: #f5f7f5;
                padding: 30px 22px;
            }

            .mobile-brand {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 45px;
            }

            .mobile-brand .brand-icon {
                background: #174b32;
            }

            .mobile-brand .brand-name {
                color: #174b32;
            }
        }

        .login-divider {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 22px 0;
    color: #777;
    font-size: 12px;
}

.login-divider::before,
.login-divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background: #ddd;
}

.btn-google-login {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    min-height: 46px;
    padding: 10px 16px;
    border: 1px solid #dadce0;
    border-radius: 7px;
    background: #fff;
    color: #3c4043;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s, box-shadow .2s;
}

.btn-google-login:hover {
    background: #f8f9fa;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .12);
}

.btn-google-login img {
    width: 20px;
    height: 20px;
}
    </style>
</head>

<body>

<div class="login-container">

    <section class="brand-panel">
        <div class="brand">
            <div class="brand-icon">B</div>
            <div>
                <div class="brand-name">BRO360</div>
                <div class="brand-subtitle">BANGSAMORO REVENUE OFFICE</div>
            </div>
        </div>

        <div class="brand-content">
            <div class="eyebrow">INTEGRATED OFFICE MANAGEMENT</div>

            <h1>
                Everything in one place.
                <span>One office. One system.</span>
            </h1>

            <p>
                A centralized digital workspace for managing programs,
                activities, tasks, budgets, documents, and office operations.
            </p>
        </div>

        <div class="brand-footer">
            © {{ date('Y') }} Bangsamoro Revenue Office. All rights reserved.
        </div>
    </section>

    <main class="form-panel">
        <div class="login-card">

            <div class="mobile-brand">
                <div class="brand-icon">B</div>
                <div>
                    <div class="brand-name">BRO360</div>
                    <div class="brand-subtitle">BANGSAMORO REVENUE OFFICE</div>
                </div>
            </div>

            <h2>Welcome back</h2>
            <p class="description">
                Sign in to access your BRO360 workspace.
            </p>

            @if ($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email address"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="input-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="password-input"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword()"
                        >
                            SHOW
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="login-button">
                    Sign In to BRO360
                </button>
                
                <div class="login-divider">
    <span>OR</span>
</div>

<a href="{{ route('auth.google') }}" class="btn-google-login">
    <img
        src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg"
        alt="Google"
    >

    <span>Sign in with Google</span>
</a>
            </form>

            <div class="security-note">
                <p>Authorized personnel only.</p>
                <p>Protected access to BRO360 internal systems.</p>
                <p><a href="/privacy-policy">Privacy Policy</a></p>
            </div>

        </div>
    </main>

</div>

<script>
    function togglePassword() {
        const password = document.getElementById('password');
        const button = document.querySelector('.toggle-password');

        if (password.type === 'password') {
            password.type = 'text';
            button.textContent = 'HIDE';
        } else {
            password.type = 'password';
            button.textContent = 'SHOW';
        }
    }
</script>

</body>
</html>
