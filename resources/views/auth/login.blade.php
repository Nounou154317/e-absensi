<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — E-Absensi SMK Muhammadiyah 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            /* Background gedung sekolah */
            background-image:
                url('{{ asset("img/Sekolah.jpg") }}'),
                url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #93c5fd;
        }

        /* Overlay supaya teks terbaca */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(147, 197, 253, 0.25);
            pointer-events: none;
        }

        /* ============ LOGIN CARD ============ */
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 380px;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.15),
                0 0 0 6px rgba(67, 56, 202, 0.9);  /* garis ungu di atas */
            padding: 45px 38px 32px;
            position: relative;
            overflow: hidden;
        }

        /* Aksen ungu tebal di atas card */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #4338ca, #6366f1);
            border-radius: 16px 16px 0 0;
        }

        /* ============ HEADER ============ */
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .login-header h2 {
            font-size: 15px;
            font-weight: 700;
            color: #1e3a8a;
            letter-spacing: 0.5px;
        }

        /* ============ FORM ============ */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px;
            font-size: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            color: #1e293b;
            transition: all 0.2s;
            outline: none;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            background: #fff;
            border-color: #4338ca;
            box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.1);
        }

        /* ============ BUTTON ============ */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #4338ca;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }

        .btn-login:hover {
            background: #3730a3;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(67, 56, 202, 0.35);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* ============ FORGOT ============ */
        .forgot-wrapper {
            text-align: right;
            margin-top: 14px;
        }

        .forgot-link {
            font-size: 12px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            color: #4338ca;
        }

        /* ============ ERROR ============ */
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border-left: 3px solid #ef4444;
        }

        /* ============ DEMO HINT (opsional) ============ */
        .demo-hint {
            text-align: center;
            margin-top: 22px;
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .demo-hint code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            color: #4338ca;
            font-size: 10px;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 480px) {
            .login-card { padding: 36px 24px 26px; }
            .login-header h1 { font-size: 19px; }
            .login-header h2 { font-size: 14px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-header">
            <h1>SMK MUHAMMADIYAH 1</h1>
            <h2>E - Absensi Login</h2>
        </div>

        @if($errors->any())
            <div class="alert-error">
                <i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email">Username</label>
                <input type="email" id="email" name="email"
                       class="form-control"
                       value="{{ old('email') }}"
                       placeholder=""
                       autocomplete="email"
                       required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       class="form-control"
                       placeholder=""
                       autocomplete="current-password"
                       required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="forgot-wrapper">
            <a href="#" class="forgot-link">Forgot Password?</a>
        </div>

        <div class="demo-hint">
            <strong>Demo:</strong>
            <code>admin@sekolah.com</code> ·
            <code>guru@sekolah.com</code> ·
            <code>siswa@sekolah.com</code><br>
            Password: <code>password</code>
        </div>

    </div>
</div>

</body>
</html>