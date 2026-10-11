@extends('layouts.app')
@section('title', 'Semua Absensi')

@section('content')

{{-- ============ HEADER ============ --}}
<h3 class="page-title">Semua Absensi</h3>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div style="font-size: 13px; color: #64748b;">
        Data absensi seluruh siswa — SMK Muhammadiyah 1 Tangerang
    </div>
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <input type="date" name="tanggal" class="filter-input"
               value="{{ request('tanggal') }}"
               onchange="this.form.submit()">
        <select name="kelas" class="filter-input" onchange="this.form.submit()">
            <option value="">Semua Kelas</option>
            @foreach($kelasList as $k)
                <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
        </select>
        @if(request()->filled('tanggal') || request()->filled('kelas'))
            <a href="{{ route('admin.absensi') }}" class="filter-reset">Reset</a>
        @endif
    </form>
</div>

{{-- ============ STATISTIK RINGKAS ============ --}}
@php
    $totalHariIni = $absensis->where('tanggal', now()->toDateString())->count();
    $hadirCount   = $absensis->where('status', 'hadir')->count();
    $izinCount    = $absensis->where('status', 'izin')->count();
    $sakitCount   = $absensis->where('status', 'sakit')->count();
    $alphaCount   = $absensis->where('status', 'alpha')->count();
@endphp

<div class="row g-2 mb-3">
    <div class="col">
        <div class="summary-card" style="background: #4338ca;">
            <div class="num">{{ $absensis->total() }}</div>
            <div class="lbl">Total Record</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: #10b981;">
            <div class="num">{{ $hadirCount }}</div>
            <div class="lbl">Hadir</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: #06b6d4;">
            <div class="num">{{ $izinCount }}</div>
            <div class="lbl">Izin</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: #f59e0b;">
            <div class="num">{{ $sakitCount }}</div>
            <div class="lbl">Sakit</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: #ef4444;">
            <div class="num">{{ $alphaCount }}</div>
            <div class="lbl">Alpha</div>
        </div>
    </div>
</div>

{{-- ============ TABEL ABSENSI ============ --}}
<div class="card-flat">
    <div class="card-head" style="background: #f8fafc;">
        <h6 style="margin: 0; font-weight: 700;">
            Data Seluruh Absensi
            @if(request('tanggal'))
                <span style="font-weight: 400; color: #64748b; font-size: 12px;">
                    — {{ \Carbon\Carbon::parse(request('tanggal'))->translatedFormat('d F Y') }}
                </span>
            @endif
        </h6>
        <div style="font-size: 12px; color: #64748b;">
            {{ $absensis->total() }} record
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="table-absensi">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Tanggal</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th class="text-center">Masuk</th>
                    <th class="text-center">Foto Masuk</th>
                    <th class="text-center">Pulang</th>
                    <th class="text-center">Foto Pulang</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensis as $i => $a)
                @php
                    $isTerlambat = $a->jam_masuk && $a->jam_masuk > '07:00:00' && $a->status == 'hadir';
                @endphp
                <tr>
                    <td style="color: #94a3b8;">{{ $absensis->firstItem() + $i }}</td>
                    <td>
                        <div style="font-weight: 500; color: #1e293b;">
                            {{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}
                        </div>
                        <div style="font-size: 11px; color: #94a3b8;">
                            {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('l') }}
                        </div>
                    </td>
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
                            <img src="{{ $a->foto_masuk }}" alt="Foto Masuk" class="w-12 h-12 object-cover rounded-lg">
                            <span style="color: #cbd5e1;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($a->jam_pulang)
                            <span style="font-weight: 600; color: #10b981;">
                                {{ substr($a->jam_pulang, 0, 5) }}
                            </span>
                        @else
                            <span style="color: #cbd5e1;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($a->foto_pulang)
                            <img src="{{ asset('storage/'.$a->foto_pulang) }}"
                                 class="foto-absen"
                                 onclick="showFoto('{{ asset('storage/'.$a->foto_pulang) }}', '{{ $a->siswa->nama }}', 'pulang', '{{ $a->jam_pulang }}')">
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
                    <td colspan="10" style="text-align: center; padding: 50px; color: #94a3b8;">
                        <i class="bi bi-inbox" style="font-size: 40px; display: block; margin-bottom: 10px; opacity: .5;"></i>
                        Belum ada data absensi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($absensis->hasPages())
    <div class="card-footer" style="padding: 14px 20px; border-top: 1px solid #f1f5f9; background: #f8fafc;">
        {{ $absensis->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- ============ MODAL LIHAT FOTO ============ --}}
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-body p-4 text-center">
                <h5 id="fotoTitle" style="font-weight: 700; color: #0f172a; font-size: 16px; margin-bottom: 4px;"></h5>
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
    /* ============ HEADER FILTER ============ */
    .filter-input {
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        border: 1px solid #e2e8f0;
        background: #fff;
        outline: none;
        color: #334155;
        transition: 0.15s;
    }
    .filter-input:focus {
        border-color: #4338ca;
        box-shadow: 0 0 0 3px rgba(67,56,202,0.1);
    }
    .filter-reset {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        text-decoration: none;
        font-weight: 500;
    }
    .filter-reset:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* ============ KARTU STATISTIK ============ */
    .summary-card {
        border-radius: 10px;
        padding: 14px 12px;
        color: #fff;
        text-align: center;
        min-height: 78px;
    }
    .summary-card .num {
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
    }
    .summary-card .lbl {
        font-size: 11px;
        margin-top: 6px;
        opacity: 0.95;
        font-weight: 500;
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
</style>
@endpush

@push('scripts')
<script>
    // Tampilkan foto di modal
    function showFoto(src, nama, tipe, jam) {
        document.getElementById('fotoLarge').src = src;
        document.getElementById('fotoTitle').textContent = 'Foto Absen ' + tipe.charAt(0).toUpperCase() + tipe.slice(1);
        document.getElementById('fotoSubtitle').textContent = nama + ' • ' + (jam ? jam.substring(0, 5) : '-');
        new bootstrap.Modal(document.getElementById('modalFoto')).show();
    }
</script>
@endpush