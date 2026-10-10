@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')

{{-- ============ HEADER ============ --}}
<h3 class="page-title">Dashboard Admin</h3>

{{-- ============ BANNER SAMBUTAN ============ --}}
<div class="welcome-banner">
    <div>
        <h4>Halo, {{ auth()->user()->name }} 👋</h4>
        <div class="meta">
            Anda login sebagai <strong>Administrator</strong>
        </div>
        <span class="badge-tahun">Sistem E-Absensi SMK Muhammadiyah 1 Tangerang</span>
    </div>
    <div class="clock-box">
        <div class="clock live-clock">--:--:--</div>
        <div class="date">{{ now()->translatedFormat('l, d F Y') }}</div>
    </div>
</div>

{{-- ============ 3 KARTU STATISTIK UTAMA ============ --}}
<div class="row g-3 mb-3">

    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #4338ca, #6366f1);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-title">Total Siswa</div>
                    <div class="stat-num">{{ $totalSiswa }}</div>
                    <div class="stat-desc">siswa terdaftar</div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #34d399);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-title">Total Guru</div>
                    <div class="stat-num">{{ $totalGuru }}</div>
                    <div class="stat-desc">guru aktif</div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-title">Absensi Hari Ini</div>
                    <div class="stat-num">{{ $absensiHariIni }}</div>
                    <div class="stat-desc">record masuk</div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-clipboard-check-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ SHORTCUT MENU ============ --}}
<div class="card-flat">
    <div class="card-head" style="background: #f8fafc;">
        <h6 style="margin: 0; font-weight: 700;">
            <i class="bi bi-lightning-charge-fill" style="color: #f59e0b;"></i> Menu Cepat
        </h6>
    </div>
    <div class="card-body" style="padding: 20px;">
        <div class="row g-3">

            <div class="col-md-4">
                <a href="{{ route('admin.users') }}" class="menu-shortcut">
                    <div class="menu-icon" style="background: #ede9fe; color: #4338ca;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="menu-text">
                        <div class="menu-title">Kelola User</div>
                        <div class="menu-desc">Tambah, edit, hapus akun siswa & guru</div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('admin.absensi') }}" class="menu-shortcut">
                    <div class="menu-icon" style="background: #d1fae5; color: #10b981;">
                        <i class="bi bi-clipboard-check-fill"></i>
                    </div>
                    <div class="menu-text">
                        <div class="menu-title">Semua Absensi</div>
                        <div class="menu-desc">Pantau kehadiran seluruh siswa</div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="#" class="menu-shortcut" style="opacity: .6; pointer-events: none;">
                    <div class="menu-icon" style="background: #fef3c7; color: #f59e0b;">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <div class="menu-text">
                        <div class="menu-title">Pengaturan</div>
                        <div class="menu-desc">Segera hadir</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ============ INFO TAMBAHAN ============ --}}
<div class="row g-3 mt-1">

    <div class="col-md-6">
        <div class="card-flat">
            <div class="card-head" style="background: #f8fafc;">
                <h6 style="margin: 0; font-weight: 700;">
                    <i class="bi bi-info-circle-fill" style="color: #4338ca;"></i> Tentang Sistem
                </h6>
            </div>
            <div class="card-body" style="padding: 16px 20px; font-size: 13px; color: #475569; line-height: 1.7;">
                <p style="margin: 0 0 8px 0;">
                    Sistem <strong>E-Absensi SMK Muhammadiyah 1 Tangerang</strong> digunakan untuk mencatat
                    kehadiran siswa secara digital dengan verifikasi <strong>foto selfie</strong>.
                </p>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Absensi dilakukan 2x: masuk & pulang</li>
                    <li>Foto tersimpan otomatis di storage</li>
                    <li>Guru & admin dapat memantau rekap harian</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="catatan-penting" style="margin-top: 0;">
            <div class="title">Catatan Penting :</div>
            <ol>
                <li>Maksimal Absen Masuk Di Jam 7:30 WIB.</li>
                <li>Jika Terlambat Harap Konfirmasi Ke Tata Usaha.</li>
                <li>Pengajuan izin/sakit hanya dapat dilakukan orang tua.</li>
            </ol>
        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
    /* ============ BANNER ============ */
    .welcome-banner {
        background: #4338ca;
        color: #fff;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 12px;
    }
    .welcome-banner h4 {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 4px 0;
    }
    .welcome-banner .meta {
        font-size: 13px;
        opacity: 0.9;
        margin-bottom: 10px;
    }
    .welcome-banner .badge-tahun {
        display: inline-block;
        background: rgba(255,255,255,0.15);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        border: 1px solid rgba(255,255,255,0.25);
    }
    .welcome-banner .clock-box {
        text-align: right;
    }
    .welcome-banner .clock {
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
        font-family: monospace;
    }
    .welcome-banner .date {
        font-size: 11px;
        opacity: 0.85;
        margin-top: 6px;
    }

    /* ============ STAT CARD ============ */
    .stat-card {
        border-radius: 12px;
        padding: 20px 22px;
        color: #fff;
        min-height: 130px;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0,0,0,0.15);
    }
    .stat-card .stat-title {
        font-size: 13px;
        font-weight: 500;
        opacity: 0.95;
        margin-bottom: 8px;
    }
    .stat-card .stat-num {
        font-size: 42px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-card .stat-desc {
        font-size: 11px;
        opacity: 0.85;
    }
    .stat-card .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    /* ============ MENU SHORTCUT ============ */
    .menu-shortcut {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none;
        color: #1e293b;
        transition: all 0.2s;
        height: 100%;
    }
    .menu-shortcut:hover {
        border-color: #4338ca;
        background: #fafbff;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(67,56,202,0.1);
        color: #4338ca;
    }
    .menu-shortcut .menu-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .menu-shortcut .menu-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }
    .menu-shortcut .menu-desc {
        font-size: 12px;
        color: #64748b;
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 768px) {
        .welcome-banner {
            text-align: center;
        }
        .welcome-banner .clock-box {
            text-align: center;
            width: 100%;
        }
        .stat-card .stat-num {
            font-size: 32px;
        }
    }
</style>
@endpush