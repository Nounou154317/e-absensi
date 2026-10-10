@extends('layouts.app')
@section('title', 'Dashboard Guru')

@section('content')

{{-- ============ HEADER ============ --}}
<h3 class="page-title">Dashboard Guru</h3>

{{-- ============ BANNER SAMBUTAN ============ --}}
<div class="welcome-banner">
    <div>
        <h4>Halo, {{ auth()->user()->name }} 👋</h4>
        <div class="meta">
            Anda login sebagai <strong>Guru</strong>
            @if($penugasan['jumlah_mapel'] > 0)
                — mengampu {{ $penugasan['jumlah_mapel'] }} mata pelajaran di {{ $penugasan['jumlah_kelas'] }} kelas
            @endif
        </div>
        <span class="badge-tahun">SMK Muhammadiyah 1 Tangerang</span>
    </div>
    <div class="clock-box">
        <div class="clock live-clock">--:--:--</div>
        <div class="date">{{ now()->translatedFormat('l, d F Y') }}</div>
    </div>
</div>

{{-- ============ MATA PELAJARAN YANG DIAMPU ============ --}}
@include('guru._penugasan')

{{-- ============ 3 KARTU STATISTIK UTAMA ============ --}}
<div class="row g-3 mb-3">

    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #4338ca, #6366f1);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-title">Total Siswa</div>
                    <div class="stat-num">{{ $totalSiswa }}</div>
                    <div class="stat-desc">siswa di kelas yang Anda ajar</div>
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
                    <div class="stat-title">Hadir Hari Ini</div>
                    <div class="stat-num">{{ $hadirHariIni }}</div>
                    <div class="stat-desc">dari {{ $totalSiswa }} siswa</div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-title">Belum Absen</div>
                    <div class="stat-num">{{ max(0, $totalSiswa - $hadirHariIni) }}</div>
                    <div class="stat-desc">siswa belum absen</div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ MENU CEPAT ============ --}}
<div class="card-flat mb-3">
    <div class="card-head" style="background: #f8fafc;">
        <h6 style="margin: 0; font-weight: 700;">
            <i class="bi bi-lightning-charge-fill" style="color: #f59e0b;"></i> Menu Cepat
        </h6>
    </div>
    <div class="card-body" style="padding: 20px;">
        <div class="row g-3">

            <div class="col-md-6">
                <a href="{{ route('guru.rekap') }}" class="menu-shortcut">
                    <div class="menu-icon" style="background: #ede9fe; color: #4338ca;">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <div class="menu-text">
                        <div class="menu-title">Rekap Absensi</div>
                        <div class="menu-desc">Lihat & filter rekap kehadiran siswa di kelas Anda</div>
                    </div>
                </a>
            </div>

            <div class="col-md-6">
                <a href="{{ route('guru.nilai.index') }}" class="menu-shortcut">
                    <div class="menu-icon" style="background: #d1fae5; color: #10b981;">
                        <i class="bi bi-clipboard-data-fill"></i>
                    </div>
                    <div class="menu-text">
                        <div class="menu-title">Input Nilai</div>
                        <div class="menu-desc">Input nilai mata pelajaran & kelas yang Anda ampu</div>
                    </div>
                </a>
            </div>

        </div>
    </div>
</div>

{{-- ============ TABEL ABSENSI HARI INI ============ --}}
<div class="card-flat">
    <div class="card-head" style="background: #f8fafc;">
        <h6 style="margin: 0; font-weight: 700;">
            <i class="bi bi-clipboard-check-fill" style="color: #4338ca;"></i>
            Absensi Hari Ini
        </h6>
        <div style="font-size: 12px; color: #64748b;">
            {{ $absensiHariIni->count() }} record
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="table-absensi">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th class="text-center">Jam Masuk</th>
                    <th class="text-center">Foto</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensiHariIni as $i => $a)
                @php
                    $isTerlambat = $a->jam_masuk && $a->jam_masuk > '07:00:00' && $a->status == 'hadir';
                @endphp
                <tr>
                    <td style="color: #94a3b8;">{{ $i + 1 }}</td>
                    <td style="font-family: monospace; color: #475569;">
                        {{ $a->siswa->nis ?? '-' }}
                    </td>
                    <td>
                        <div style="font-weight: 500; color: #1e293b;">
                            {{ $a->siswa->nama ?? '-' }}
                        </div>
                    </td>
                    <td>
                        <span class="badge-kelas">{{ $a->siswa->kelas ?? '-' }}</span>
                    </td>
                    <td class="text-center">
                        @if($a->jam_masuk)
                            <span style="font-weight: 600; color: {{ $isTerlambat ? '#f59e0b' : '#10b981' }};">
                                {{ substr($a->jam_masuk, 0, 5) }}
                            </span>
                        @else
                            <span style="color: #cbd5e1;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($a->foto_masuk)
                            <img src="{{ asset('storage/'.$a->foto_masuk) }}"
                                 class="foto-absen"
                                 onclick="showFoto('{{ asset('storage/'.$a->foto_masuk) }}', '{{ $a->siswa->nama }}', '{{ $a->jam_masuk }}')">
                        @else
                            <span style="color: #cbd5e1;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($isTerlambat)
                            <span class="badge-status status-terlambat">Terlambat</span>
                        @else
                            <span class="badge-status status-{{ $a->status }}">
                                {{ ucfirst($a->status) }}
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 50px; color: #94a3b8;">
                        <i class="bi bi-inbox" style="font-size: 40px; display: block; margin-bottom: 10px; opacity: .5;"></i>
                        Belum ada absensi hari ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ============ CATATAN PENTING ============ --}}
<div class="catatan-penting mt-4">
    <div class="title">Catatan Penting :</div>
    <ol>
        <li>Maksimal Absen Masuk Di Jam 7:30 WIB.</li>
        <li>Jika Terlambat Harap Konfirmasi Ke Tata Usaha.</li>
        <li>Pengajuan izin/sakit hanya dapat dilakukan orang tua.</li>
    </ol>
</div>

{{-- ============ MODAL LIHAT FOTO ============ --}}
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-body p-4 text-center">
                <h5 id="fotoTitle" style="font-weight: 700; color: #0f172a; font-size: 16px; margin-bottom: 4px;">Foto Absen</h5>
                <p id="fotoSubtitle" style="color: #64748b; font-size: 12px; margin-bottom: 16px;"></p>

                <img id="fotoLarge" src="" alt="Foto Absen"
                     style="width: 100%; border-radius: 12px; display: block;">

                <button class="btn btn-secondary btn-sm mt-3"
                        data-bs-dismiss="modal"
                        style="border-radius: 8px; padding: 8px 24px;">
                    Tutup
                </button>
            </div>
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

    /* ============ TABEL ============ */
    .table-absensi {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-absensi th {
        padding: 12px 14px;
        background: #f8fafc;
        text-align: left;
        color: #64748b;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .table-absensi td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-absensi tr:last-child td { border-bottom: none; }
    .table-absensi tbody tr:hover { background: #fafbfc; }
    .table-absensi .text-center { text-align: center; }

    /* ============ FOTO ============ */
    .foto-absen {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: 0.2s;
    }
    .foto-absen:hover {
        transform: scale(1.15);
        border-color: #4338ca;
        box-shadow: 0 4px 12px rgba(67,56,202,0.3);
    }

    /* ============ BADGE ============ */
    .badge-kelas {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 6px;
        background: #ede9fe;
        color: #4338ca;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    .status-hadir    { background: #d1fae5; color: #065f46; }
    .status-izin     { background: #dbeafe; color: #1e40af; }
    .status-sakit    { background: #e9d5ff; color: #6b21a8; }
    .status-alpha    { background: #fee2e2; color: #991b1b; }
    .status-terlambat{ background: #fef3c7; color: #92400e; }

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

@push('scripts')
<script>
    // Tampilkan foto di modal
    function showFoto(src, nama, jam) {
        document.getElementById('fotoLarge').src = src;
        document.getElementById('fotoSubtitle').textContent = nama + ' • ' + (jam ? jam.substring(0, 5) : '-');
        new bootstrap.Modal(document.getElementById('modalFoto')).show();
    }
</script>
@endpush