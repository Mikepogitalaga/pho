
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($isRegister) && $isRegister ? 'Create Account' : 'Sign In' }} — PHO Supply Office</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>

        /* =========================================================
           RESET
        ========================================================= */

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-family: "Poppins", sans-serif;
        }

       body {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                linear-gradient(
                    rgba(30, 30, 30, 0.30),
                    rgba(30, 30, 30, 0.30)
                ),
                url("{{ asset('bg.jpg') }}");

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;

            overflow-x: hidden;
            overflow-y: auto;
        }


        /* =========================================================
           MAIN WRAPPER
        ========================================================= */

        .login-wrapper {
            width: 100%;
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 30px;
        }


        /* =========================================================
           FLIP SCENE
        ========================================================= */

        .flip-scene {
            width: 520px;
            height: 520px;

            perspective: 1800px;
        }


        .flip-card {
            position: relative;

            width: 100%;
            height: 100%;

            transform-style: preserve-3d;

            transition:
                transform .9s cubic-bezier(.22,1,.36,1);
        }


        .flip-scene.flipped .flip-card {
            transform: rotateY(180deg);
        }


        /* =========================================================
           CIRCULAR NEUMORPHIC CARD
        ========================================================= */

        .glass-circle {
            position: absolute;

            inset: 0;

            width: 520px;
            height: 520px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #e8e8e8;

            box-shadow:
                -22px -22px 44px #ffffff,
                22px 22px 50px #c3c3c3,
                -1px -1px 1px rgba(255,255,255,.5) inset;

            z-index: 5;

            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }


        /* Inner ring */

        .glass-circle::before {
            content: "";

            position: absolute;

            inset: 12px;

            border-radius: 50%;

            border: 1px solid rgba(255,255,255,.65);

            box-shadow:
                inset 3px 3px 9px rgba(163,163,163,.18),
                inset -3px -3px 9px rgba(255,255,255,.7);

            pointer-events: none;
        }


        /* Back side */

        .glass-circle.back {
            transform: rotateY(180deg);
        }


        /* =========================================================
           PHO LOGO
        ========================================================= */

        .pho-logo {
            position: absolute;

            top: 32px;
            left: 50%;

            transform: translateX(-50%);

            z-index: 20;
        }


        .pho-logo img {
            width: 70px;
            height: 70x;

            object-fit: cover;

            border-radius: 100%;

            padding: 5px;

            background: #e8e8e8;

            box-shadow:
                -6px -6px 12px #ffffff,
                6px 6px 12px #c3c3c3;
        }


        /* =========================================================
           FORM
        ========================================================= */

        .login-form {
            position: relative;

            width: 290px;

            z-index: 10;

            margin-top: 75px;
        }


        /* =========================================================
           TITLE
        ========================================================= */

        .login-form h1 {
            color: #4a4a4a;

            font-size: 40px;
            font-weight: 700;

            letter-spacing: .5px;

            margin-bottom: 4px;

            text-align: center;

            text-shadow:
                1px 1px 1px rgba(255,255,255,.9),
                -2px -2px 1px rgba(163,163,163,.25);
        }


        .subtitle {
            text-align: center;

            color: #9a9a9a;

            font-size: 12px;
            font-weight: 500;

            letter-spacing: .3px;

            margin-bottom: 26px;
        }


        /* =========================================================
           ERROR MESSAGE
        ========================================================= */

        .error-pill {
            margin-bottom: 15px;

            padding: 9px 12px;

            border-radius: 10px;

            color: #a10f0f;

            background: #e8e8e8;

            font-size: 10.5px;

            text-align: center;

            box-shadow:
                inset 3px 3px 6px rgba(163,163,163,.22),
                inset -3px -3px 6px rgba(255,255,255,.8);
        }


        /* =========================================================
           INPUT GROUP
        ========================================================= */

        .field {
            position: relative;

            margin-bottom: 17px;
        }


        .field label {
            display: none;
        }


        .field input {
            width: 100%;
            height: 46px;

            padding:
                0 43px
                0 45px;

            border: none;
            outline: none;

            border-radius: 12px;

            background: #e8e8e8;

            color: #4a4a4a;

            font-family: inherit;

            font-size: 14px;
            font-weight: 500;

            box-shadow:
                inset -6px -6px 10px rgba(255,255,255,.95),
                inset 6px 6px 10px rgba(184,190,204,.45);

            transition:
                box-shadow .4s cubic-bezier(.22,1,.36,1),
                transform .4s cubic-bezier(.22,1,.36,1);
        }


        .field input::placeholder {
            color: #a3a3a3;

            font-weight: 400;
        }


        .field input:hover {
            box-shadow:
                inset -5px -5px 9px rgba(255,255,255,.95),
                inset 5px 5px 9px rgba(184,190,204,.45);
        }


        .field input:focus {
            transform: translateY(-2px);

            box-shadow:
                inset 3px 3px 6px rgba(120,10,10,.35),
                inset -3px -3px 6px rgba(212,55,55,.25);
        }


        .field input.is-invalid {
            box-shadow:
                inset 3px 3px 6px rgba(160,15,15,.28),
                inset -3px -3px 6px rgba(255,255,255,.9);
        }


        /* =========================================================
           INPUT ICON
        ========================================================= */

        .field-icon {
            position: absolute;

            left: 17px;
            top: 23px;

            transform: translateY(-50%);

            color: #909090;

            font-size: 14px;

            pointer-events: none;

            transition:
                color .3s ease,
                transform .3s ease;
        }


        .field input:focus + .field-icon {
            color: #a10f0f;

            transform:
                translateY(-50%)
                scale(1.08);
        }


        /* =========================================================
           PASSWORD VISIBILITY
        ========================================================= */

        .toggle-visibility {
            position: absolute;

            right: 9px;
            top: 23px;

            transform: translateY(-50%);

            width: 30px;
            height: 30px;

            display: flex;
            align-items: center;
            justify-content: center;

            border: none;
            outline: none;

            border-radius: 50%;

            background: transparent;

            color: #999;

            cursor: pointer;

            transition:
                color .3s ease,
                background .3s ease;
        }


        .toggle-visibility:hover {
            color: #a10f0f;

            background: rgba(255,255,255,.35);
        }


        /* =========================================================
           FIELD ERROR
        ========================================================= */

        .form-error {
            margin-top: 5px;

            padding-left: 5px;

            color: #a10f0f;

            font-size: 10px;

            font-weight: 500;
        }


        .field-help {
            margin-top: 5px;

            padding-left: 5px;

            color: #999;

            font-size: 9.5px;
        }


        /* =========================================================
           REMEMBER + FORGOT
        ========================================================= */

        .form-row {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            margin-top: -2px;

            margin-bottom: 27px;
        }


        .checkbox {
            display: flex;

            align-items: center;

            gap: 8px;

            color: #929191;

            font-size: 11px;

            cursor: pointer;
        }


        .checkbox input {
            width: 14px;
            height: 14px;

            accent-color: #a10f0f;

            cursor: pointer;
        }


        .inline-link {
            color: #929191;

            font-size: 11px;

            font-weight: 500;

            text-decoration: none;

            transition:
                color .3s ease;
        }


        .inline-link:hover {
            color: #a10f0f;
        }


        /* =========================================================
           MAIN BUTTON
        ========================================================= */

        .auth-btn {
            position: relative;

            width: 290px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            border: none;
            outline: none;

            border-radius: 12px;

            background: #e8e8e8;

            color: #838383;

            font-family: inherit;

            font-size: 14px;

            font-weight: 600;

            letter-spacing: 1px;

            text-transform: uppercase;

            cursor: pointer;

            box-shadow:
                -7px -7px 12px #f8f8f8,
                7px 7px 12px #c8c8c8;

            transition:
                color .4s ease,
                background .4s ease,
                box-shadow .4s ease,
                transform .25s ease;
        }


        /* Button shine */

        .auth-btn::before {
            content: "";

            position: absolute;

            top: 0;
            left: -60%;

            width: 40%;
            height: 100%;

            background:
                linear-gradient(
                    115deg,
                    transparent,
                    rgba(255,255,255,.55),
                    transparent
                );

            transform: skewX(-20deg);

            transition:
                left .6s ease;
        }


        .auth-btn:hover::before {
            left: 130%;
        }


        .auth-btn:hover {
            transform: translateY(-2px);

            color: #f5e9c8;

            background:
                linear-gradient(
                    155deg,
                    #b21414 0%,
                    #7c0d0d 100%
                );

            box-shadow:
                0 10px 22px rgba(120,10,10,.35),
                -5px -5px 15px rgba(255,255,255,.6),
                inset 0 1px 1px rgba(255,255,255,.25);
        }


        .auth-btn:active {
            transform: scale(.97);

            box-shadow:
                inset 5px 5px 10px rgba(60,5,5,.4),
                inset -5px -5px 10px rgba(212,175,55,.2);
        }


        /* Loading */

        .auth-btn.is-loading {
            pointer-events: none;

            background:
                linear-gradient(
                    155deg,
                    #b21414,
                    #7c0d0d
                );

            color: #f5e9c8;
        }


        .auth-btn.is-loading::after {
            content: "";

            position: absolute;

            width: 18px;
            height: 18px;

            border-radius: 50%;

            border:
                2px solid rgba(255,255,255,.4);

            border-top-color: #fff;

            animation: spin .7s linear infinite;
        }


        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }


            /* =========================================================
            SWITCH / REGISTER TEXT
            ========================================================= */

        .signup-text {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 5px;

                margin-top: 10px;

                color: #9a9a9a;
                font-size: 11px;

                white-space: nowrap;
            }

                .signup-text button {
                    display: inline;
                    
                    border: none;
                    padding: 0;

                    background: transparent;

                    color: #de0a0a;

                    font-family: inherit;
                    font-size: inherit;
                    font-weight: 600;

                    cursor: pointer;

                    white-space: nowrap;
                }

                .signup-text button:hover {
                    color: #7c0d0d;
                    text-decoration: underline;
                }

        /* =========================================================
           FORM PANELS
        ========================================================= */

        .form-panel {
            display: none;

            opacity: 0;

            transform: translateX(15px);
        }


        .form-panel.active {
            display: block;

            opacity: 1;

            transform: translateX(0);

            animation:
                formAppear .45s ease;
        }


        @keyframes formAppear {
            from {
                opacity: 0;
                transform: translateX(15px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 600px) {

            .login-wrapper {
                padding: 15px;
            }

            .flip-scene {
                width: 390px;
                height: 390px;
            }

            .glass-circle {
                width: 390px;
                height: 390px;
            }

            .login-form {
                width: 220px;

                margin-top: 35px;
            }

            .pho-logo {
                top: 36px;
            }

            .pho-logo img {
                width: 40px;
                height: 40px;
            }

            .login-form h1 {
                font-size: 33px;
            }

            .subtitle {
                font-size: 10.5px;

                margin-bottom: 20px;
            }

            .field {
                margin-bottom: 13px;
            }

            .field input {
                height: 42px;

                font-size: 12px;

                padding-left: 40px;
            }

            .field-icon {
                left: 14px;

                top: 21px;

                font-size: 13px;
            }

            .toggle-visibility {
                top: 21px;
            }

            .form-row {
                margin-bottom: 21px;
            }

            .checkbox,
            .inline-link {
                font-size: 9px;
            }

            .auth-btn {
                width: 215px;

                height: 40px;

                font-size: 12px;
            }

            .signup-text {
                font-size: 9px;
            }
        }


        /* =========================================================
           SMALL MOBILE
        ========================================================= */

        @media (max-width: 420px) {

            .login-wrapper {
                padding: 5px;
            }

            .flip-scene {
                width: 330px;
                height: 330px;
            }

            .glass-circle {
                width: 330px;
                height: 330px;
            }

            .login-form {
                width: 185px;

                margin-top: 23px;
            }

            .pho-logo {
                top: 27px;
            }

            .pho-logo img {
                width: 40px;
                height: 40px;
            }

            .login-form h1 {
                font-size: 26px;
            }

            .subtitle {
                font-size: 8.5px;

                margin-bottom: 14px;
            }

            .field {
                margin-bottom: 10px;
            }

            .field input {
                height: 35px;

                font-size: 10px;

                padding-left: 32px;
            }

            .field-icon {
                left: 10px;

                top: 17.5px;

                font-size: 10px;
            }

            .toggle-visibility {
                top: 17.5px;

                width: 25px;
                height: 25px;
            }

            .form-row {
                margin-bottom: 16px;
            }

            .checkbox,
            .inline-link {
                font-size: 7.5px;
            }

            .checkbox input {
                width: 11px;
                height: 11px;
            }

            .auth-btn {
                width: 180px;

                height: 36px;

                font-size: 10px;

                border-radius: 10px;
            }

            .signup-text {
                margin-top: 12px;

                font-size: 7.5px;
            }

            .field-help {
                font-size: 7px;
            }

            .form-error {
                font-size: 7.5px;
            }
        }


        /* =========================================================
           REDUCED MOTION
        ========================================================= */

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
        }

    </style>
</head>


<body>


<div class="login-wrapper">

    <div class="flip-scene" id="flipScene">

        <div class="flip-card">


            {{-- =====================================================
                 LOGIN SIDE
            ====================================================== --}}

            <div class="glass-circle front">


                {{-- PHO LOGO --}}
                <div class="pho-logo">

                    <img
                        src="{{ asset('logo.jpg') }}"
                        alt="PHO Supply Office Logo"
                    >

                </div>


                <div class="login-form">


                    <div class="form-panel active" id="loginPanel">


                        <h1>Login</h1>

                        <div class="subtitle">
                            Sign in to PHO Supply Office
                        </div>


                        {{-- Session Error --}}
                        @if(session('error'))

                            <div class="error-pill">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                {{ session('error') }}

                            </div>

                        @endif


                        <form
                            method="POST"
                            action="{{ route('login.attempt') }}"
                            class="form-stack"
                            id="loginForm"
                        >

                            @csrf


                            {{-- EMAIL --}}
                            <div class="field">

                                <label for="login_email">
                                    Email address
                                </label>

                                <input
                                    id="login_email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                    required
                                    autocomplete="email"
                                    placeholder="Email address"
                                >

                                <span class="field-icon">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>


                                @error('email')

                                    <div class="form-error">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- PASSWORD --}}
                            <div class="field">

                                <label for="login_password">
                                    Password
                                </label>

                                <input
                                    id="login_password"
                                    name="password"
                                    type="password"
                                    class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Password"
                                >

                                <span class="field-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </span>


                                <button
                                    type="button"
                                    class="toggle-visibility"
                                    data-target="login_password"
                                    aria-label="Show password"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>


                                @error('password')

                                    <div class="form-error">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- REMEMBER / FORGOT --}}
                            <div class="form-row">

                                <label class="checkbox">

                                    <input
                                        type="checkbox"
                                        name="remember"
                                        {{ old('remember') ? 'checked' : '' }}
                                    >

                                    <span>Remember me</span>

                                </label>


                                @if(Route::has('password.request'))

                                    <a
                                        class="inline-link"
                                        href="{{ route('password.request') }}"
                                    >
                                        Forgot Password?
                                    </a>

                                @else

                                    <a
                                        class="inline-link"
                                        href="#"
                                    >
                                        Forgot Password?
                                    </a>

                                @endif

                            </div>


                            {{-- LOGIN BUTTON --}}
                            <button
                                type="submit"
                                class="auth-btn"
                                id="loginSubmit"
                            >

                                <i class="fa-solid fa-right-to-bracket"
                                   style="margin-right:8px;"></i>

                                Sign In

                            </button>

                        </form>


                        {{-- CREATE ACCOUNT --}}
                        <div class="signup-text">

                            Don't have an account?

                            <button
                                type="button"
                                id="toSignup"
                            >
                                Create Account
                            </button>

                        </div>


                    </div>

                </div>

            </div>


            {{-- =====================================================
                 REGISTER SIDE
            ====================================================== --}}

            <div class="glass-circle back">


                {{-- PHO LOGO --}}
                <div class="pho-logo">

                    <img
                        src="{{ asset('logo.jpg') }}"
                        alt="PHO Supply Office Logo"
                    >

                </div>


                <div class="login-form">


                    <div class="form-panel active">


                        <h1>Sign Up</h1>

                        


                        <form
                            method="POST"
                            action="{{ route('register.store') }}"
                            class="form-stack"
                            id="registerForm"
                        >

                            @csrf


                            {{-- NAME --}}
                            <div class="field">

                                <label for="register_name">
                                    Full name
                                </label>

                                <input
                                    id="register_name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name') }}"
                                    class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    required
                                    autocomplete="name"
                                    placeholder="Full name"
                                >

                                <span class="field-icon">
                                    <i class="fa-solid fa-user"></i>
                                </span>


                                @error('name')

                                    <div class="form-error">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- EMAIL --}}
                            <div class="field">

                                <label for="register_email">
                                    Email address
                                </label>

                                <input
                                    id="register_email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                    required
                                    autocomplete="email"
                                    placeholder="Email address"
                                >

                                <span class="field-icon">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>


                                @error('email')

                                    <div class="form-error">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- PASSWORD --}}
                            <div class="field">

                                <label for="register_password">
                                    Password
                                </label>

                                <input
                                    id="register_password"
                                    name="password"
                                    type="password"
                                    class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Password"
                                >

                                <span class="field-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </span>


                                <button
                                    type="button"
                                    class="toggle-visibility"
                                    data-target="register_password"
                                    aria-label="Show password"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>


                                @error('password')

                                    <div class="form-error">
                                        {{ $message }}
                                    </div>

                                @enderror

                                <div class="field-help">
                                    Use at least 8 characters with letters and numbers.
                                </div>

                            </div>


                            {{-- CONFIRM PASSWORD --}}
                            <div class="field">

                                <label for="register_password_confirmation">
                                    Confirm password
                                </label>

                                <input
                                    id="register_password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Confirm password"
                                >

                                <span class="field-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </span>


                                <button
                                    type="button"
                                    class="toggle-visibility"
                                    data-target="register_password_confirmation"
                                    aria-label="Show password"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                            </div>


                            {{-- REGISTER BUTTON --}}
                            <button
                                type="submit"
                                class="auth-btn"
                                id="registerSubmit"
                            >

                                <i class="fa-solid fa-user-plus"
                                   style="margin-right:8px;"></i>

                                Create Account

                            </button>

                        </form>


                        {{-- LOGIN --}}
                        <div class="signup-text">

                            Already have an account?

                            <button
                                type="button"
                                id="toLogin"
                            >
                                Login
                            </button>

                        </div>


                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



<script>

    /* =========================================================
       LOGIN / REGISTER FLIP
    ========================================================= */

    const scene =
        document.getElementById('flipScene');


    const toSignup =
        document.getElementById('toSignup');


    const toLogin =
        document.getElementById('toLogin');


    if (toSignup) {

        toSignup.addEventListener('click', function () {

            scene.classList.add('flipped');

        });

    }


    if (toLogin) {

        toLogin.addEventListener('click', function () {

            scene.classList.remove('flipped');

        });

    }



    /* =========================================================
       PASSWORD VISIBILITY
    ========================================================= */

    document
        .querySelectorAll('.toggle-visibility')
        .forEach(function(button) {

            button.addEventListener('click', function() {

                const target =
                    document.getElementById(
                        this.dataset.target
                    );


                if (!target) {
                    return;
                }


                const icon =
                    this.querySelector('i');


                if (target.type === 'password') {

                    target.type = 'text';

                    icon.classList.remove(
                        'fa-eye'
                    );

                    icon.classList.add(
                        'fa-eye-slash'
                    );

                    this.setAttribute(
                        'aria-label',
                        'Hide password'
                    );

                } else {

                    target.type = 'password';

                    icon.classList.remove(
                        'fa-eye-slash'
                    );

                    icon.classList.add(
                        'fa-eye'
                    );

                    this.setAttribute(
                        'aria-label',
                        'Show password'
                    );

                }

            });

        });



    /* =========================================================
       LOGIN LOADING
    ========================================================= */

    const loginForm =
        document.getElementById('loginForm');


    const loginSubmit =
        document.getElementById('loginSubmit');


    if (loginForm && loginSubmit) {

        loginForm.addEventListener('submit', function() {

            loginSubmit.classList.add(
                'is-loading'
            );

            loginSubmit.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Signing in...';

        });

    }



    /* =========================================================
       REGISTER LOADING
    ========================================================= */

    const registerForm =
        document.getElementById('registerForm');


    const registerSubmit =
        document.getElementById('registerSubmit');


    if (registerForm && registerSubmit) {

        registerForm.addEventListener('submit', function() {

            registerSubmit.classList.add(
                'is-loading'
            );

            registerSubmit.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Creating...';

        });

    }



    /* =========================================================
       OPEN REGISTER AUTOMATICALLY WHEN VALIDATION ERROR
       ========================================================= */

    @if(
        $errors->has('name') ||
        $errors->has('password_confirmation')
    )

        scene.classList.add('flipped');

    @endif

</script>


</body>
</html>
```
