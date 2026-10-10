<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Absensi') — SMK Muhammadiyah 1 Tangerang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --topbar: #4338ca;
            --sidebar-text: #64748b;
            --sidebar-active: #ede9fe;
            --sidebar-active-text: #4338ca;
            --banner: #4338ca;
        }

        * {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #f8fafc;
            margin: 0;
        }

        /* ============ TOPBAR ============ */
        .topbar {
            background: #4338ca;
            color: #fff;
            padding: 0 30px;
            height: 64px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-left .school-logo {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #fbbf24;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid #fff;
            flex-shrink: 0;
        }

        .topbar-left .school-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .topbar-left .school-name {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            color: #fff;
        }

        .topbar-left .school-name small {
            display: block;
            font-size: 11px;
            font-weight: 400;
            opacity: 0.85;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-user {
            text-align: right;
            font-size: 12px;
            line-height: 1.3;
        }

        .topbar-user .name {
            font-weight: 700;
        }

        .topbar-user .role {
            opacity: 0.85;
            font-size: 11px;
        }

        .topbar-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            color: #4338ca;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            border: 2px solid rgba(255, 255, 255, 0.6);
            flex-shrink: 0;
        }

        /* ============ BADGE MENU ============ */
        .nav-badge {
            display: inline-block;
            min-width: 18px;
            padding: 1px 6px;
            margin-left: 6px;
            border-radius: 10px;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
        }

        /* ============ LAYOUT ============ */
        .layout {
            display: flex;
            min-height: calc(100vh - 64px);
        }

        /* ============ SIDEBAR ============ */
        .sidebar {
            width: 200px;
            background: #fff;
            border-right: 1px solid #e2e8f0;
            padding: 20px 0;
            flex-shrink: 0;
        }

        .sidebar-brand {
            padding: 0 18px 18px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 12px;
        }

        .sidebar-brand h5 {
            font-size: 15px;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0;
        }

        .sidebar-brand small {
            font-size: 11px;
            color: #94a3b8;
        }

        .sidebar a.nav-item {
            display: block;
            padding: 11px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.15s;
            margin: 2px 0;
        }

        .sidebar a.nav-item:hover {
            background: #f8fafc;
            color: var(--sidebar-active-text);
        }

        .sidebar a.nav-item.active {
            background: var(--sidebar-active);
            color: var(--sidebar-active-text);
            font-weight: 700;
            border-left: 3px solid var(--sidebar-active-text);
        }

        .sidebar .logout-item {
            color: #dc2626 !important;
            margin-top: 10px;
        }

        .sidebar .logout-item:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        /* ============ MAIN CONTENT ============ */
        .main-content {
            flex: 1;
            padding: 20px 24px;
            min-width: 0;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 18px;
        }

        /* ============ BANNER UNGU ============ */
        .welcome-banner {
            background: var(--banner);
            color: #fff;
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .welcome-banner h4 {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }

        .welcome-banner .meta {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .welcome-banner .badge-tahun {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .welcome-banner .clock-box {
            text-align: right;
        }

        .welcome-banner .clock {
            font-size: 24px;
            font-weight: 700;
            line-height: 1;
        }

        .welcome-banner .date {
            font-size: 11px;
            opacity: 0.9;
            margin-top: 4px;
        }

        /* ============ CARD ABSEN ============ */
        .absen-card {
            border-radius: 10px;
            padding: 16px 18px;
            height: 100%;
            border: 1px solid;
        }

        .absen-card.masuk {
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .absen-card.pulang {
            background: #fff1f2;
            border-color: #fecdd3;
        }

        .absen-card .title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .absen-card .info {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .absen-btn {
            width: 100%;
            border: none;
            border-radius: 6px;
            padding: 10px;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            transition: 0.15s;
        }

        .absen-btn.masuk {
            background: #10b981;
        }

        .absen-btn.masuk:hover {
            background: #059669;
        }

        .absen-btn.pulang {
            background: #f43f5e;
        }

        .absen-btn.pulang:hover {
            background: #e11d48;
        }

        .absen-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ============ STATISTIK ============ */
        .statistik-card {
            background: #fff;
            border-radius: 10px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            height: 100%;
        }

        .statistik-card .head {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-box {
            border-radius: 8px;
            padding: 10px 8px;
            text-align: center;
        }

        .stat-box .num {
            font-size: 20px;
            font-weight: 700;
            line-height: 1;
        }

        .stat-box .lbl {
            font-size: 10px;
            margin-top: 4px;
        }

        .stat-box.hadir {
            background: #d1fae5;
            color: #065f46;
        }

        .stat-box.terlambat {
            background: #fef3c7;
            color: #92400e;
        }

        .stat-box.sakit {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .stat-box.tidak {
            background: #fecdd3;
            color: #9f1239;
        }

        /* ============ RIWAYAT TABLE ============ */
        .card-flat {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .card-flat .card-head {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-flat .card-body {
            padding: 20px;
        }

        .card-flat .card-head h6 {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .filter-group {
            display: flex;
            gap: 8px;
        }

        .filter-group select {
            font-size: 11px;
            padding: 4px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fff;
            color: #475569;
            outline: none;
        }

        /* Tabel riwayat */
        .table-riwayat {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .table-riwayat th {
            background: #f8fafc;
            padding: 10px 12px;
            text-align: left;
            color: #475569;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-riwayat td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .table-riwayat tr:last-child td {
            border-bottom: none;
        }

        /* Badge status */
        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        .status-hadir {
            background: #d1fae5;
            color: #065f46;
        }

        .status-terlambat {
            background: #fef3c7;
            color: #92400e;
        }

        .status-izin {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-sakit {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .status-alpha {
            background: #fecdd3;
            color: #9f1239;
        }

        .status-belum {
            background: #f1f5f9;
            color: #64748b;
        }

        .note-text {
            font-size: 11px;
            color: #dc2626;
            font-style: italic;
        }

        /* ============ JADWAL ============ */
        .jadwal-list {
            padding: 12px 18px 18px;
        }

        .jadwal-item {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px dashed #f1f5f9;
            font-size: 12px;
        }

        .jadwal-item:last-child {
            border-bottom: none;
        }

        .jadwal-time {
            color: #4338ca;
            font-weight: 600;
            min-width: 78px;
        }

        .jadwal-mapel {
            color: #334155;
        }

        /* ============ CATATAN PENTING ============ */
        .catatan-penting {
            background: #fef9c3;
            border: 1px solid #fde047;
            border-radius: 10px;
            padding: 14px 18px;
            margin-top: 16px;
        }

        .catatan-penting .title {
            font-size: 12px;
            font-weight: 700;
            color: #854d0e;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .catatan-penting ol {
            margin: 0;
            padding-left: 20px;
            font-size: 12px;
            color: #713f12;
            line-height: 1.7;
        }

        /* ============ TOAST ============ */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
        }

        .toast-custom {
            background: #fff;
            border-radius: 8px;
            padding: 12px 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            min-width: 280px;
            margin-bottom: 10px;
            border-left: 4px solid #4338ca;
            animation: slideIn 0.3s;
        }

        .toast-custom.success {
            border-left-color: #10b981;
        }

        .toast-custom.danger {
            border-left-color: #ef4444;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ============ MODAL KAMERA ============ */
        .camera-modal .modal-content {
            border: none;
            border-radius: 12px;
        }

        .camera-modal video {
            width: 100%;
            border-radius: 8px;
            background: #000;
            transform: scaleX(-1);
        }

        .camera-modal canvas {
            display: none;
        }

        .camera-modal #preview {
            width: 100%;
            border-radius: 8px;
            display: none;
        }

        /* ============ RESPONSIF (ponsel & tablet) ============ */
        @media (max-width: 768px) {
            .topbar {
                padding: 0 14px;
            }

            .topbar-user {
                display: none;
            }

            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                display: flex;
                overflow-x: auto;
                padding: 0;
                border-right: 0;
                border-bottom: 1px solid #e2e8f0;
            }

            .sidebar-brand {
                display: none;
            }

            .sidebar a.nav-item {
                white-space: nowrap;
                border-left: 0;
                border-bottom: 3px solid transparent;
                margin: 0;
                padding: 12px 16px;
            }

            .sidebar a.nav-item.active {
                border-left: 0;
                border-bottom: 3px solid var(--sidebar-active-text);
            }

            .sidebar .logout-item {
                margin-top: 0;
            }

            .main-content {
                padding: 14px;
            }

            .page-title {
                font-size: 19px;
            }

            .welcome-banner {
                flex-direction: column;
                gap: 12px;
            }

            .welcome-banner .clock-box {
                text-align: left;
            }
        }
    </style>
    @stack('styles')
</head>

<body>

    <!-- ============ TOPBAR ============ -->
    <div class="topbar">
        {{-- KIRI: Logo + Nama Sekolah --}}
        <div class="topbar-left">
            <div class="school-logo">
                <img src="{{ asset('img/Logo.jpg') }}" alt="Logo">
            </div>
            <div class="school-name">
                SMK MUHAMMADIYAH 1
                <small>TANGERANG</small>
            </div>
        </div>

        {{-- KANAN: Nama User + Avatar --}}
        <div class="topbar-right">
            <div class="topbar-user">
                <div class="name">{{ auth()->user()->name }}</div>
                <div class="role">
                    @if(auth()->user()->isSiswa())
                        Siswa • {{ auth()->user()->kelas }}
                    @elseif(auth()->user()->isOrangTua())
                        Orang Tua Siswa
                    @else
                        {{ ucfirst(auth()->user()->role) }}
                    @endif
                </div>
            </div>
            <div class="topbar-avatar">
                @php
                    $words = explode(' ', auth()->user()->name);$initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1));
                @endphp
                {{ $initials }}
            </div>
        </div>
    </div>

    <!-- ============ LAYOUT ============ -->
    <div class="layout">

        <!-- ============ SIDEBAR ============ -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h5>E - Absensi</h5>
                <small>{{ auth()->user()->role === 'orang_tua' ? 'Portal Orang Tua' : 'Portal Siswa' }}</small>
            </div>

            @if(auth()->user()->role === 'admin')
            <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            href="{{ route('admin.dashboard') }}">
                Dashboard
            </a>
            <a class="nav-item {{ request()->routeIs('admin.siswa*') ? 'active' : '' }}"
            href="{{ route('admin.siswa') }}">
                Kelola Siswa
            </a>
            <a class="nav-item {{ request()->routeIs('admin.mapel*') ? 'active' : '' }}"
            href="{{ route('admin.mapel.index') }}">
                Mata Pelajaran
            </a>
            <a class="nav-item {{ request()->routeIs('admin.penugasan*') ? 'active' : '' }}"
            href="{{ route('admin.penugasan.index') }}">
                Penugasan Guru
            </a>
            <a class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}"
            href="{{ route('admin.users') }}">
                Kelola User
            </a>
            <a class="nav-item {{ request()->routeIs('admin.absensi') ? 'active' : '' }}"
            href="{{ route('admin.absensi') }}">
                Semua Absensi
            </a>

            @elseif(auth()->user()->role === 'guru')
                <a class="nav-item {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}"
                   href="{{ route('guru.dashboard') }}">
                    Dashboard
                </a>
                <a class="nav-item {{ request()->routeIs('guru.rekap') ? 'active' : '' }}"
                   href="{{ route('guru.rekap') }}">
                    Rekap Absensi
                </a>
                <a class="nav-item {{ request()->routeIs('guru.nilai*') ? 'active' : '' }}"
                   href="{{ route('guru.nilai.index') }}">
                    Input Nilai
                </a>
                    @php $jmlPengajuan = \App\Models\PengajuanIzin::where('status', 'menunggu')->whereHas('siswa', fn ($s) => $s->whereIn('kelas', auth()->user()->kelasDiampu()))
                    ->count(); @endphp
                <a class="nav-item {{ request()->routeIs('guru.pengajuan*') ? 'active' : '' }}"
                   href="{{ route('guru.pengajuan.index') }}">
                    Pengajuan Izin
                    @if($jmlPengajuan > 0)<span class="nav-badge">{{ $jmlPengajuan }}</span>@endif
                </a>

            @elseif(auth()->user()->role === 'siswa')
                <a class="nav-item {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}"
                   href="{{ route('siswa.dashboard') }}">
                    Dashboard
                </a>
                <a class="nav-item {{ request()->routeIs('siswa.riwayat') ? 'active' : '' }}"
                   href="{{ route('siswa.riwayat') }}">
                    Riwayat Absen
                </a>
                <a class="nav-item {{ request()->routeIs('siswa.nilai') ? 'active' : '' }}"
                   href="{{ route('siswa.nilai') }}">
                    Riwayat Nilai
                </a>

            @elseif(auth()->user()->role === 'orang_tua')
                <a class="nav-item {{ request()->routeIs('orang_tua.dashboard') ? 'active' : '' }}"
                   href="{{ route('orang_tua.dashboard') }}">
                    Dashboard
                </a>
                <a class="nav-item {{ request()->routeIs('orang_tua.kalender') ? 'active' : '' }}"
                   href="{{ route('orang_tua.kalender') }}">
                    Kalender &amp; Rekap
                </a>
                <a class="nav-item {{ request()->routeIs('orang_tua.izin*') ? 'active' : '' }}"
                   href="{{ route('orang_tua.izin') }}">
                    Ajukan Izin / Sakit
                </a>
            @endif

            <a class="nav-item logout-item" href="#"
               onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                Log out
            </a>
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </aside>

        <!-- MAIN -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <!-- TOAST -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(msg, type = 'success', title = null) {
            const icons = {
                success: 'check-circle-fill',
                danger: 'x-circle-fill',
                warning: 'exclamation-triangle-fill'
            };
            const colors = {
                success: '#10b981',
                danger: '#ef4444',
                warning: '#f59e0b'
            };
            const el = document.createElement('div');
            el.className = 'toast-custom ' + type;
            el.innerHTML = `
                <div style="display:flex; gap:12px;">
                    <i class="bi bi-${icons[type] || 'info-circle-fill'}" style="color:${colors[type]}; font-size:20px;"></i>
                    <div>
                        <div style="font-weight:600; font-size:13px; color:#0f172a;">${title ?? (type === 'success' ? 'Berhasil' : 'Informasi')}</div>
                        <div style="font-size:12px; color:#64748b;">${msg}</div>
                    </div>
                </div>`;
            document.getElementById('toastContainer').appendChild(el);
            setTimeout(() => el.remove(), 4000);
        }

        function updateClocks() {
            const now = new Date();
            const time = now.toLocaleTimeString('id-ID', { hour12: false }).replace(/\./g, ':');
            document.querySelectorAll('.live-clock').forEach(el => el.textContent = time);
        }
        setInterval(updateClocks, 1000);
        updateClocks();
    </script>
    @stack('scripts')
</body>

</html>