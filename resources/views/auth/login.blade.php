<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — AbsensiRB</title>
    <meta name="description" content="Login ke sistem presensi karyawan AbsensiRB">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563EB">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --primary: #2563EB;
            --primary-dark: #1D4ED8;
            --primary-light: #60A5FA;
            --primary-50: #EFF6FF;
            --bg-body: #F8FAFC;
            --gray-100: #F1F5F9;
            --gray-200: #E2E8F0;
            --gray-400: #94A3B8;
            --gray-500: #64748B;
            --gray-800: #1E293B;
            --gray-900: #0F172A;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-body);
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 24px;
            padding: 40px 32px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.05);
            border: 1px solid var(--gray-100);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo .icon {
            width: 56px; height: 56px;
            background: var(--primary-50);
            color: var(--primary);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 16px;
        }
        .login-logo h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.01em;
        }
        .login-logo h1 span { color: var(--primary); }
        .login-logo p {
            font-size: 0.85rem;
            color: var(--gray-500);
            margin-top: 4px;
        }
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 8px;
        }
        .input-icon-wrap {
            position: relative;
        }
        .input-icon-wrap .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1.1rem;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--gray-900);
            transition: all 0.2s;
            background: white;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-50);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-login:active { transform: none; }
        
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert i { font-size: 1.1rem; margin-top: 2px; }
        .alert-error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }
        .alert-success {
            background: var(--primary-50);
            border: 1px solid #BFDBFE;
            color: var(--primary-dark);
        }
        .login-footer {
            text-align: center;
            margin-top: 32px;
            font-size: 0.78rem;
            color: var(--gray-400);
        }
        .back-link {
            position: absolute;
            top: 24px;
            left: 24px;
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--gray-500);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--gray-900); }
    </style>
</head>
<body>
<a href="/" class="back-link"><i class="ph ph-arrow-left"></i> Kembali ke Mesin Presensi</a>

<div class="login-card">
    <div class="login-logo">
        <div class="icon"><i class="ph ph-fingerprint"></i></div>
        <h1>Absensi<span>RB</span></h1>
        <p>Sistem Presensi Digital Karyawan</p>
    </div>

    @if($errors->any())
    <div class="alert alert-error">
        <i class="ph ph-warning-circle"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success">
        <i class="ph ph-check-circle"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="form-group">
            <label class="form-label" for="login">Email atau Username</label>
            <div class="input-icon-wrap">
                <i class="ph ph-user icon"></i>
                <input
                    type="text"
                    id="login"
                    name="login"
                    class="form-control"
                    value="{{ old('login') }}"
                    placeholder="Masukkan kredensial..."
                    autocomplete="username"
                    required
                >
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-icon-wrap">
                <i class="ph ph-lock-key icon"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </div>
        </div>
        <button type="submit" id="btn-login" class="btn-login">
            Masuk <i class="ph ph-sign-in"></i>
        </button>
    </form>

    <div class="login-footer">
        © {{ date('Y') }} AbsensiRB · Hubungi Tim IT jika lupa password
    </div>
</div>
</body>
</html>
