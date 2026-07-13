<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($isRegister) && $isRegister ? 'Create Account' : 'Sign In' }} — PHO Supply Office</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        :root {
            color-scheme: light;
            --auth-bg: linear-gradient(135deg, #0f172a 0%, #111827 45%, #1e3a8a 100%);
            --panel-bg: rgba(255,255,255,0.96);
            --panel-border: rgba(255,255,255,0.24);
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --accent: #7c3aed;
            --danger: #dc2626;
            --success: #16a34a;
            --shadow: 0 24px 60px rgba(2, 6, 23, 0.22);
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; min-height: 100%; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: var(--auth-bg);
            color: var(--text);
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            inset: auto;
            border-radius: 999px;
            filter: blur(16px);
            opacity: 0.55;
            pointer-events: none;
            animation: float 8s ease-in-out infinite;
        }

        body::before {
            width: 280px; height: 280px; left: -90px; top: -70px; background: rgba(59,130,246,0.35);
        }

        body::after {
            width: 320px; height: 320px; right: -120px; bottom: -90px; background: rgba(124,58,237,0.24); animation-delay: 2s;
        }

        .auth-shell {
            position: relative;
            width: min(1120px, calc(100% - 2rem));
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: rgba(255,255,255,0.16);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .auth-hero {
            position: relative;
            padding: 2rem 2rem 2.5rem;
            background: linear-gradient(135deg, rgba(37,99,235,0.9), rgba(124,58,237,0.88));
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 620px;
        }

        .auth-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.28), transparent 24%), radial-gradient(circle at bottom left, rgba(255,255,255,0.15), transparent 18%);
            pointer-events: none;
        }

        .hero-top, .hero-bottom { position: relative; z-index: 1; }
        .hero-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.6rem 0.8rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.16);
            width: fit-content;
        }

        .hero-mark img { width: 44px; height: 44px; border-radius: 0.9rem; object-fit: cover; }
        .hero-title { margin: 1.25rem 0 0.6rem; font-size: clamp(1.7rem, 3vw, 2.45rem); line-height: 1.12; font-weight: 800; }
        .hero-copy { margin: 0; max-width: 420px; color: rgba(255,255,255,0.9); line-height: 1.7; }

        .hero-card {
            margin-top: 1.8rem;
            padding: 1rem 1.1rem;
            border-radius: 20px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
        }

        .hero-card ul { margin: 0.15rem 0 0; padding-left: 1.05rem; color: rgba(255,255,255,0.92); display: grid; gap: 0.55rem; }

        .hero-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.7rem; border-radius: 999px; background: rgba(255,255,255,0.2); font-size: 0.8rem; font-weight: 600; }

        .auth-panel {
            position: relative;
            padding: 2rem;
            background: var(--panel-bg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-panel-inner {
            width: min(420px, 100%);
            position: relative;
        }

        .auth-switch {
            display: inline-flex;
            padding: 0.35rem;
            border-radius: 999px;
            background: #eef2ff;
            gap: 0.3rem;
            margin-bottom: 1.4rem;
        }

        .auth-switch button {
            border: 0;
            background: transparent;
            color: var(--muted);
            padding: 0.7rem 1rem;
            border-radius: 999px;
            font-weight: 700;
            transition: all 280ms cubic-bezier(.2,.8,.2,1);
        }

        .auth-switch button.active {
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            box-shadow: 0 8px 24px rgba(37,99,235,0.2);
        }

        .auth-title { margin: 0 0 0.35rem; font-size: 1.7rem; font-weight: 800; }
        .auth-copy { margin: 0 0 1.2rem; color: var(--muted); line-height: 1.6; }

        .social-row { display: grid; gap: 0.7rem; grid-template-columns: 1fr 1fr; margin-bottom: 1rem; }
        .social-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem; padding: 0.8rem 0.9rem; border-radius: 999px; border: 1px solid #e2e8f0; background: white; color: #0f172a; font-weight: 600; transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
        }
        .social-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(15,23,42,0.06); border-color: #cbd5e1; }

        .auth-divider { display: flex; align-items: center; gap: 0.75rem; color: #94a3b8; font-size: 0.82rem; margin: 0.9rem 0 1rem; }
        .auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

        .form-stack { display: flex; flex-direction: column; gap: 0.9rem; }
        .field { position: relative; }
        .field label { display: block; margin-bottom: 0.45rem; font-size: 0.9rem; font-weight: 700; color: #334155; }
        .field input {
            width: 100%; padding: 0.95rem 1rem 0.95rem 2.95rem; border: 1px solid #e2e8f0; border-radius: 16px; background: #f8fafc; color: var(--text); outline: none; transition: all 240ms ease; font-size: 0.95rem;
        }
        .field input:hover { border-color: #cbd5e1; }
        .field input:focus { border-color: #60a5fa; box-shadow: 0 0 0 4px rgba(96,165,250,0.16); background: white; }
        .field input.is-invalid { border-color: #fb7185; background: #fff5f7; }
        .field-icon {
            position: absolute; top: 50%; left: 1rem; transform: translateY(-50%); color: #94a3b8; pointer-events: none; transition: color 220ms ease;
        }
        .field input:focus + .field-icon { color: var(--primary); }

        .field .toggle-visibility {
            position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); border: 0; background: transparent; color: var(--muted); padding: 0.35rem; border-radius: 999px;
        }
        .field .toggle-visibility:hover { background: #f1f5f9; color: var(--text); }

        .field-help { margin-top: 0.35rem; font-size: 0.8rem; color: #94a3b8; }
        .form-error { margin-top: 0.35rem; font-size: 0.82rem; color: var(--danger); }

        .form-row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin: 0.2rem 0 0.1rem; }
        .checkbox { display: inline-flex; align-items: center; gap: 0.5rem; color: var(--muted); font-size: 0.9rem; }
        .checkbox input { width: 1rem; height: 1rem; accent-color: var(--primary); }
        .inline-link { color: var(--primary); font-weight: 700; text-decoration: none; }
        .inline-link:hover { text-decoration: underline; }

        .auth-btn {
            width: 100%; padding: 0.95rem 1rem; border: 0; border-radius: 999px; color: white; font-size: 0.96rem; font-weight: 700; background: linear-gradient(135deg, var(--primary), var(--accent)); box-shadow: 0 14px 30px rgba(37,99,235,0.22); transition: transform 220ms ease, box-shadow 220ms ease; position: relative; overflow: hidden; margin-top: 0.2rem;
        }
        .auth-btn:hover { transform: translateY(-2px); box-shadow: 0 18px 34px rgba(37,99,235,0.28); }
        .auth-btn:active { transform: translateY(0); }
        .auth-btn.is-loading::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.22), transparent); animation: shine 1s linear infinite; }

        .form-panel { display: none; opacity: 0; transform: translateX(16px); transition: all 420ms cubic-bezier(.2,.8,.2,1); }
        .form-panel.active { display: block; opacity: 1; transform: translateX(0); }

        .success-pill, .error-pill { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.7rem 0.8rem; border-radius: 999px; margin-top: 0.75rem; font-size: 0.9rem; font-weight: 600; }
        .success-pill { background: rgba(22,163,74,0.1); color: var(--success); }
        .error-pill { background: rgba(220,38,38,0.1); color: var(--danger); }

        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }

        @keyframes float { 0%,100% { transform: translate3d(0,0,0) scale(1); } 50% { transform: translate3d(18px,-16px,0) scale(1.04); } }
        @keyframes shine { from { transform: translateX(-100%); } to { transform: translateX(100%); } }

        @media (max-width: 920px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-hero { min-height: 320px; }
        }

        @media (max-width: 640px) {
            .auth-shell { width: min(100%, calc(100% - 1rem)); border-radius: 24px; }
            .auth-hero, .auth-panel { padding: 1.2rem; }
            .social-row { grid-template-columns: 1fr; }
            .auth-switch { width: 100%; }
            .auth-switch button { flex: 1; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; scroll-behavior: auto !important; }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <section class="auth-hero" aria-label="Brand overview">
            <div class="hero-top">
                <div class="hero-mark">
                    <img src="{{ asset('logo.jpg') }}" alt="PHO Logo">
                    <div>
                        <div style="font-size: 0.72rem; letter-spacing: 0.18em; text-transform: uppercase; opacity: 0.8;">Supply Office</div>
                        <div style="font-size: 1rem; font-weight: 700;">PHO Inventory</div>
                    </div>
                </div>
                <h1 class="hero-title">A modern hub for your inventory operations.</h1>
                <p class="hero-copy">Manage receiving, releases, suppliers, and stock updates from a single polished workspace designed for speed and clarity.</p>
                <div class="hero-card">
                    <span class="hero-badge">⚡ Fast, secure, intuitive</span>
                    <ul>
                        <li>Real-time visibility into stock movement</li>
                        <li>Professional workflows for procurement and distribution</li>
                        <li>Built-in validations and friendly feedback</li>
                    </ul>
                </div>
            </div>
            <div class="hero-bottom">
                <div class="hero-badge">Trusted by modern operations teams</div>
            </div>
        </section>

        <section class="auth-panel" aria-label="Authentication forms">
            <div class="auth-panel-inner">
                <div class="auth-switch" role="tablist" aria-label="Authentication mode">
                    <button type="button" class="active" id="show-login" data-mode="login" role="tab" aria-selected="true">Sign In</button>
                    <button type="button" data-mode="register" role="tab" aria-selected="false">Create Account</button>
                </div>

                <h2 class="auth-title" id="authTitle">Welcome back</h2>
                <p class="auth-copy" id="authCopy">Access your inventory workspace and continue managing your supply flow.</p>

                

                <div class="form-panel active" id="loginPanel">
                    <form method="POST" action="{{ route('login.attempt') }}" class="form-stack" novalidate>
                        @csrf
                        <div class="field">
                            <label for="login_email">Email address</label>
                            <input id="login_email" name="email" type="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" required autocomplete="email" placeholder="you@example.com">
                            <span class="field-icon">✉</span>
                            @error('email')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="login_password">Password</label>
                            <input id="login_password" name="password" type="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" required autocomplete="current-password" placeholder="••••••••">
                            <span class="field-icon">🔒</span>
                            <button type="button" class="toggle-visibility" data-target="login_password" aria-label="Show password">👁</button>
                            @error('password')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <label class="checkbox"><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember me</label>
                            <a class="inline-link" href="#">Forgot Password?</a>
                        </div>

                        <button type="submit" class="auth-btn" id="loginSubmit">Sign in</button>
                    </form>
                    @if(session('error'))
                        <div class="error-pill">⚠ {{ session('error') }}</div>
                    @endif
                </div>

                <div class="form-panel" id="registerPanel">
                    <form method="POST" action="{{ route('register.store') }}" class="form-stack" novalidate>
                        @csrf
                        <div class="field">
                            <label for="register_name">Full name</label>
                            <input id="register_name" name="name" type="text" value="{{ old('name') }}" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" required autocomplete="name" placeholder="Juan dela Cruz">
                            <span class="field-icon">👤</span>
                            @error('name')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="register_email">Email address</label>
                            <input id="register_email" name="email" type="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" required autocomplete="email" placeholder="you@example.com">
                            <span class="field-icon">✉</span>
                            @error('email')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="register_password">Password</label>
                            <input id="register_password" name="password" type="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" required autocomplete="new-password" placeholder="Min. 8 characters">
                            <span class="field-icon">🔒</span>
                            <button type="button" class="toggle-visibility" data-target="register_password" aria-label="Show password">👁</button>
                            @error('password')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <div class="field-help">Use at least 8 characters with a mix of letters and numbers.</div>
                        </div>

                        <div class="field">
                            <label for="register_password_confirmation">Confirm password</label>
                            <input id="register_password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Repeat your password">
                            <span class="field-icon">🔐</span>
                            <button type="button" class="toggle-visibility" data-target="register_password_confirmation" aria-label="Show password">👁</button>
                        </div>

                        <button type="submit" class="auth-btn" id="registerSubmit">Create account</button>
                    </form>
                    @if($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('password'))
                        <div class="error-pill">⚠ Something went wrong. Please try again.</div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <script>
        const switchButtons = document.querySelectorAll('.auth-switch button');
        const panels = {
            login: document.getElementById('loginPanel'),
            register: document.getElementById('registerPanel')
        };
        const authTitle = document.getElementById('authTitle');
        const authCopy = document.getElementById('authCopy');

        function setMode(mode) {
            switchButtons.forEach((button) => {
                const active = button.dataset.mode === mode;
                button.classList.toggle('active', active);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            Object.entries(panels).forEach(([key, panel]) => {
                panel.classList.toggle('active', key === mode);
            });

            if (mode === 'register') {
                authTitle.textContent = 'Create your account';
                authCopy.textContent = 'Join the PHO Supply Office and unlock streamlined inventory management.';
            } else {
                authTitle.textContent = 'Welcome back';
                authCopy.textContent = 'Access your inventory workspace and continue managing your supply flow.';
            }
        }

        switchButtons.forEach((button) => {
            button.addEventListener('click', () => setMode(button.dataset.mode));
        });

        document.querySelectorAll('.toggle-visibility').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.target);
                if (!target) return;
                const nextType = target.type === 'password' ? 'text' : 'password';
                target.type = nextType;
                button.textContent = nextType === 'password' ? '👁' : '🙈';
            });
        });

        document.querySelectorAll('.auth-btn').forEach((button) => {
            button.addEventListener('click', () => {
                if (!button.classList.contains('is-loading')) {
                    button.classList.add('is-loading');
                    button.textContent = button.id === 'loginSubmit' ? 'Signing in…' : 'Creating account…';
                    window.setTimeout(() => button.classList.remove('is-loading'), 1200);
                }
            });
        });
    </script>
</body>
</html>

