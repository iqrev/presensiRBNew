<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AbsensiRB') — Sistem Presensi Karyawan</title>
    <meta name="description" content="Sistem presensi digital dengan verifikasi wajah dan lokasi GPS">

    <!-- PWA & Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563EB">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* ─────────────── Design Tokens ─────────────── */
        :root {
            --primary: #2563EB;
            --primary-dark: #1D4ED8;
            --primary-light: #60A5FA;
            --primary-50: #EFF6FF;
            --success: #2563EB; /* Unified to primary blue for minimalist feel */
            --success-light: #EFF6FF;
            --danger: #EF4444;
            --danger-light: #FEF2F2;
            --warning: #F59E0B;
            --warning-light: #FFFBEB;
            
            --bg-body: #F8FAFC;
            --bg-card: #FFFFFF;
            
            --gray-50: #F8FAFC;
            --gray-100: #F1F5F9;
            --gray-200: #E2E8F0;
            --gray-300: #CBD5E1;
            --gray-400: #94A3B8;
            --gray-500: #64748B;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1E293B;
            --gray-900: #0F172A;
            
            --radius: 16px;
            --radius-sm: 8px;
            --radius-lg: 24px;
            --radius-full: 9999px;
            
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
            --shadow-md: 0 10px 20px rgba(15, 23, 42, 0.04);
            --shadow-lg: 0 20px 30px -10px rgba(15, 23, 42, 0.05);
        }

        /* ─────────────── Reset & Base ─────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 16px; -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-body);
            color: var(--gray-800);
            line-height: 1.5;
            min-height: 100vh;
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* ─────────────── App Shell ─────────────── */
        .app-layout { display: flex; flex-direction: column; min-height: 100vh; }
        .app-content { flex: 1; padding-bottom: 80px; }

        /* ─────────────── Top Header ─────────────── */
        .app-header {
            background: var(--bg-card);
            border-bottom: 1px solid var(--gray-100);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .app-header .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .app-header .logo-icon {
            width: 32px; height: 32px;
            background: var(--primary-50);
            color: var(--primary);
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .app-header .logo-text {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-900);
            letter-spacing: -0.01em;
        }
        .app-header .logo-text span {
            color: var(--primary);
            font-weight: 800;
        }
        .header-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-800);
        }
        .user-role {
            font-size: 0.72rem;
            color: var(--gray-500);
        }

        /* ─────────────── Bottom Navigation ─────────────── */
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: var(--bg-card);
            border-top: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 8px 0;
            padding-bottom: calc(8px + env(safe-area-inset-bottom));
            z-index: 100;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            text-decoration: none;
            color: var(--gray-400);
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.2s;
            border-radius: var(--radius-sm);
            min-width: 64px;
        }
        .nav-item:hover {
            color: var(--gray-700);
        }
        .nav-item.active {
            color: var(--primary);
        }
        .nav-item .nav-icon { font-size: 1.4rem; transition: transform 0.2s; }
        .nav-item:hover .nav-icon { transform: translateY(-2px); }
        .nav-item.active .nav-icon { transform: scale(1.1); font-weight: 700; }

        /* ─────────────── Cards ─────────────── */
        .card {
            background: var(--bg-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-100);
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        .card:hover { box-shadow: var(--shadow-md); }
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-100);
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-body { padding: 24px; }

        /* ─────────────── Buttons ─────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-family: inherit;
            line-height: 1;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-primary:active { transform: translateY(0); }
        .btn-success {
            background: var(--primary);
            color: white;
        }
        .btn-success:hover { background: var(--primary-dark); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); font-weight: 500; }
        .btn-secondary:hover { background: var(--gray-200); color: var(--gray-900); }
        .btn-outline { background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-700); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .btn-lg { padding: 14px 28px; font-size: 1rem; }
        .btn-full { width: 100%; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; }

        /* ─────────────── Alerts ─────────────── */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid transparent;
        }
        .alert i { font-size: 1.2rem; margin-top: 1px; }
        .alert-success { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary-dark); }
        .alert-danger  { background: var(--danger-light); border-color: #FECACA;  color: #991B1B; }
        .alert-info    { background: var(--gray-50); border-color: var(--gray-200); color: var(--gray-700); }

        /* ─────────────── Badges ─────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 600;
        }
        .badge-primary { background: var(--primary-50); color: var(--primary-dark); }
        .badge-success { background: var(--primary-50); color: var(--primary-dark); }
        .badge-danger  { background: var(--danger-light); color: var(--danger); }
        .badge-gray    { background: var(--gray-100); color: var(--gray-600); }

        /* ─────────────── Forms & Inputs ─────────────── */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--gray-900);
            background: #FFFFFF;
            transition: all 0.2s ease;
        }
        .form-control::placeholder { color: var(--gray-400); }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-50);
        }
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394A3B8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 16px top 50%;
            background-size: 10px auto;
            padding-right: 40px;
        }

        /* ─────────────── Tables ─────────────── */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-100);
            background: var(--bg-card);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .table th {
            padding: 14px 20px;
            background: var(--gray-50);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--gray-200);
        }
        .table td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-100);
            font-size: 0.9rem;
            color: var(--gray-800);
            vertical-align: middle;
        }
        .table tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background: var(--gray-50); }

        /* ─────────────── Page Layout ─────────────── */
        .page-container { padding: 24px 16px; max-width: 600px; margin: 0 auto; }
        .page-header { margin-bottom: 24px; }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--gray-900); letter-spacing: -0.01em; display: flex; align-items: center; gap: 8px;}
        .page-subtitle { font-size: 0.9rem; color: var(--gray-500); margin-top: 4px; }

        /* ─────────────── Utilities ─────────────── */
        .text-center { text-align: center; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        
        /* ─────────────── Spinner ─────────────── */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            width: 20px; height: 20px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        /* ─────────────── Desktop Layout (Sidebar) ─────────────── */
        @media (min-width: 768px) {
            .app-layout {
                flex-direction: row;
                height: 100vh;
                overflow: hidden;
            }
            .app-header {
                display: none; /* Hide top header on desktop if sidebar has logo, or keep it. Let's keep a simplified layout: sidebar on left, content on right */
            }
            .bottom-nav {
                display: none !important;
            }
            .desktop-sidebar {
                display: flex !important;
                flex-direction: column;
                width: 260px;
                background: var(--bg-card);
                border-right: 1px solid var(--gray-100);
                height: 100vh;
                flex-shrink: 0;
            }
            .app-content {
                flex: 1;
                height: 100vh;
                overflow-y: auto;
                padding-bottom: 24px;
            }
            .page-container {
                max-width: 1000px;
                padding: 32px 40px;
            }
        }
        
        .desktop-sidebar {
            display: none; /* Hidden on mobile */
        }
        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--gray-100);
        }
        .sidebar-menu {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--gray-600);
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: all 0.2s;
        }
        .sidebar-item:hover {
            background: var(--gray-50);
            color: var(--gray-900);
        }
        .sidebar-item.active {
            background: var(--primary-50);
            color: var(--primary);
            font-weight: 600;
        }
        .sidebar-item .nav-icon {
            font-size: 1.25rem;
        }
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--gray-100);
        }

    </style>

    @stack('styles')
</head>
<body>
<div class="app-layout">
    <!-- Desktop Sidebar -->
    @if(Auth::check() && Auth::user()->hasRole(['admin', 'superadmin']))
    <aside class="desktop-sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="logo">
                <div style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                    <div class="logo-icon" style="width: 32px; height: 32px; background: var(--primary-50); color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="ph ph-fingerprint"></i></div>
                    <div class="logo-text" style="font-size: 1.2rem; font-weight: 700; color: var(--gray-900);">Absensi<span style="color: var(--primary);">RB</span></div>
                </div>
            </a>
        </div>
        
        <div class="sidebar-menu">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--gray-400); font-weight: 700; margin: 16px 0 8px 16px; letter-spacing: 0.5px;">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="ph ph-squares-four nav-icon"></i>
                <span>Beranda</span>
            </a>
            <a href="{{ route('admin.karyawan.index') }}" class="sidebar-item {{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}">
                <i class="ph ph-users nav-icon"></i>
                <span>Pegawai</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="sidebar-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="ph ph-file-text nav-icon"></i>
                <span>Laporan</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="sidebar-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="ph ph-gear nav-icon"></i>
                <span>Setelan Sistem</span>
            </a>
            
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--gray-400); font-weight: 700; margin: 24px 0 8px 16px; letter-spacing: 0.5px;">Akun</div>
            <a href="{{ route('admin.profile.index') }}" class="sidebar-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                <i class="ph ph-user-circle nav-icon"></i>
                <span>Profil & Password</span>
            </a>
        </div>
        
        <div class="sidebar-footer">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding: 0 8px;">
                <div style="width: 36px; height: 36px; background: var(--gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--gray-600);">
                    <i class="ph ph-user"></i>
                </div>
                <div style="overflow: hidden;">
                    <div style="font-weight: 600; font-size: 0.85rem; color: var(--gray-800); white-space: nowrap; text-overflow: ellipsis;">{{ Auth::user()->name }}</div>
                    <div style="font-size: 0.75rem; color: var(--gray-500);">{{ ucfirst(Auth::user()->getRoleNames()->first() ?? '-') }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-outline btn-full" style="padding:8px; font-size:0.85rem; border-radius: var(--radius-sm); border-color: var(--gray-200); color: var(--gray-600);">
                    <i class="ph ph-sign-out"></i> Keluar
                </button>
            </form>
        </div>
    </aside>
    @endif

    <div style="flex: 1; display: flex; flex-direction: column; min-width: 0;">
        <!-- Top Header (Mobile Only) -->
        <header class="app-header">
            <a href="{{ Auth::check() && Auth::user()->hasRole(['admin','superadmin']) ? route('admin.dashboard') : '/' }}" class="logo">
                <div class="logo-icon"><i class="ph ph-fingerprint"></i></div>
                <div>
                    <div class="logo-text">Absensi<span>RB</span></div>
                </div>
            </a>
            
            @auth
            <div class="header-user">
                <a href="{{ route('admin.profile.index') }}" style="color:var(--gray-600); margin-right: 12px;"><i class="ph ph-user-circle" style="font-size: 1.5rem;"></i></a>
            </div>
            @else
            <div class="header-user">
                <a href="{{ route('login') }}" style="color:var(--gray-400);text-decoration:none;font-size:0.8rem;padding:8px; display:flex; align-items:center; gap:6px; font-weight:500;">
                    <i class="ph ph-lock-key" style="font-size:1.1rem;"></i> Admin
                </a>
            </div>
            @endauth
        </header>

        <!-- Main Content -->
        <main class="app-content">
            @if(session('success'))
                <div style="padding:16px 20px 0;">
                    <div class="alert alert-success">
                        <i class="ph ph-check-circle"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div style="padding:16px 20px 0;">
                    <div class="alert alert-danger">
                        <i class="ph ph-warning-circle"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                </div>
            @endif
            @if($errors->any())
                <div style="padding:16px 20px 0;">
                    <div class="alert alert-danger">
                        <i class="ph ph-warning-circle"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bottom Navigation (Admin, mobile) -->
    @if(Auth::check() && Auth::user()->hasRole(['admin', 'superadmin']))
    <nav class="bottom-nav">
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="ph ph-squares-four nav-icon"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ route('admin.karyawan.index') }}" class="nav-item {{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}">
            <i class="ph ph-users nav-icon"></i>
            <span>Pegawai</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="ph ph-file-text nav-icon"></i>
            <span>Laporan</span>
        </a>
        <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="ph ph-gear nav-icon"></i>
            <span>Setelan</span>
        </a>
    </nav>
    @endif
</div>

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
</script>

@stack('scripts')
</body>
</html>
