<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — PHO Supply Office</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            margin: 0; padding: 0; height: 100%;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: #0f172a;
        }

        .auth-hero {
            display: none;
            flex: 1;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #6366f1 100%);
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }

        @media (min-width: 900px) { .auth-hero { display: flex; } }

        .auth-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("{{ asset('bg.jpg') }}") center/cover no-repeat;
            opacity: 0.15;
        }

        .auth-hero-content {
            position: relative;
            z-index: 1;
            color: #fff;
            max-width: 420px;
        }

        .auth-hero-logo {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 3rem;
        }

        .auth-hero-logo img {
            width: 52px; height: 52px;
            border-radius: 1rem;
            border: 2px solid rgba(255,255,255,0.25);
            object-fit: cover;
        }

        .auth-hero-logo-text { line-height: 1.2; }
        .auth-hero-logo-text span { display: block; font-size: 0.7rem; letter-spacing: 0.15em; text-transform: uppercase; opacity: 0.7; }
        .auth-hero-logo-text strong { font-size: 1.1rem; font-weight: 700; }

        .auth-hero h1 {
            margin: 0 0 1rem;
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 700;
            line-height: 1.2;
        }

        .auth-hero p {
            margin: 0;
            font-size: 1rem;
            opacity: 0.8;
            line-height: 1.7;
        }

        .auth-hero-features {
            margin-top: 2.5rem;
            display: grid;
            gap: 0.85rem;
        }

        .auth-hero-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            opacity: 0.85;
        }

        .auth-hero-feature-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.6);
            flex-shrink: 0;
        }

        .auth-panel {
            width: 100%;
            max-width: 480px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background: #ffffff;
        }

        @media (min-width: 900px) { .auth-panel { min-height: 100vh; } }

        .auth-panel-inner {
            width: 100%;
            max-width: 380px;
        }

        .auth-mobile-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
        }

        @media (min-width: 900px) { .auth-mobile-logo { display: none; } }

        .auth-mobile-logo img {
            width: 44px; height: 44px;
            border-radius: 0.75rem;
            object-fit: cover;
        }

        .auth-mobile-logo-text span { display: block; font-size: 0.65rem; letter-spacing: 0.12em; text-transform: uppercase; color: #64748b; }
        .auth-mobile-logo-text strong { font-size: 1rem; font-weight: 700; color: #0f172a; }

        .auth-heading {
            margin: 0 0 0.4rem;
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
        }

        .auth-subheading {
            margin: 0 0 2rem;
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .auth-form .form-group {
            margin-bottom: 1.1rem;
        }

        .auth-form label {
            display: block;
            margin-bottom: 0.45rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
        }

        .auth-form input[type="email"],
        .auth-form input[type="password"],
        .auth-form input[type="text"] {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            color: #0f172a;
            background: #f8fafc;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .auth-form input:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
        }

        .auth-form input.is-invalid {
            border-color: #ef4444;
            background: #fff5f5;
        }

        .form-error {
            margin: 0.35rem 0 0;
            font-size: 0.8rem;
            color: #dc2626;
        }

        .auth-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 0.875rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s, background 0.15s;
            margin-top: 0.5rem;
        }

        .auth-btn-primary {
            background: linear-gradient(135deg, #1d4ed8, #4f46e5);
            color: #fff;
            box-shadow: 0 8px 20px rgba(29,78,216,0.3);
        }

        .auth-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(29,78,216,0.38);
        }

        .auth-btn-primary:active { transform: translateY(0); }

        .auth-alert {
            margin-top: 1rem;
            padding: 0.85rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
        }

        .auth-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .auth-footer {
            margin-top: 1.75rem;
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
        }

        .auth-footer a {
            color: #1d4ed8;
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover { text-decoration: underline; }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .hint {
            margin: 0.3rem 0 0;
            font-size: 0.78rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    {{-- Hero panel --}}
    <div class="auth-hero">
        <div class="auth-hero-content">
            <div class="auth-hero-logo">
                <img src="{{ asset('logo.jpg') }}" alt="PHO Logo">
                <div class="auth-hero-logo-text">
                    <span>Supply Office</span>
                    <strong>PHO Inventory</strong>
                </div>
            </div>
            <h1>Join the PHO Supply Office system.</h1>
            <p>Create your account to start managing inventory, suppliers, and distribution records.</p>
            <div class="auth-hero-features">
                <div class="auth-hero-feature"><span class="auth-hero-feature-dot"></span> Real-time inventory tracking</div>
                <div class="auth-hero-feature"><span class="auth-hero-feature-dot"></span> Supplier & receiving management</div>
                <div class="auth-hero-feature"><span class="auth-hero-feature-dot"></span> Release & distribution records</div>
            </div>
        </div>
    </div>

    {{-- Form panel --}}
    <div class="auth-panel">
        <div class="auth-panel-inner">

            <div class="auth-mobile-logo">
                <img src="{{ asset('logo.jpg') }}" alt="PHO Logo">
                <div class="auth-mobile-logo-text">
                    <span>Supply Office</span>
                    <strong>PHO Inventory</strong>
                </div>
            </div>

            <h1 class="auth-heading">Create account</h1>
            <p class="auth-subheading">Fill in your details to get started.</p>

            <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text"
                           value="{{ old('name') }}"
                           class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                           required autofocus autocomplete="name"
                           placeholder="Juan dela Cruz">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email') }}"
                           class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                           required autocomplete="email"
                           placeholder="you@example.com">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                           required autocomplete="new-password"
                           placeholder="Min. 8 characters">
                    <p class="hint">At least 8 characters.</p>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           required autocomplete="new-password"
                           placeholder="Repeat your password">
                </div>

                <button type="submit" class="auth-btn auth-btn-primary">Create account</button>
            </form>

            @if($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('password'))
                <div class="auth-alert auth-alert-error">Something went wrong. Please try again.</div>
            @endif

            <div class="auth-footer">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </div>

        </div>
    </div>

</body>
</html>
