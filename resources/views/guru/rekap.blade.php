@extends('layouts.app')
@section('title', 'Rekap Absensi')

@section('content')

{{-- ============ HEADER ============ --}}
<h3 class="page-title">Rekap Absensi</h3>
<div style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
    Data kehadiran siswa — SMK Muhammadiyah 1 Tangerang
</div>

{{-- ============ FILTER ============ --}}
<div class="card-flat mb-3">
    <div class="card-head" style="background: #f8fafc;">
        <h6 style="margin: 0; font-weight: 700;">
            <i class="bi bi-funnel-fill" style="color: #4338ca;"></i> Filter Data
        </h6>
        @if(request()->hasAny(['tanggal', 'kelas', 'status']))
            <a href="{{ route('guru.rekap') }}" class="filter-reset">
                <i class="bi bi-x-circle"></i> Reset
            </a>
        @endif
    </div>
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="filter-label">Tanggal</label>
                <input type="date" name="tanggal" class="filter-input w-100"
                       value="{{ request('tanggal') }}">
            </div>
            <div class="col-md-3">
                <label class="filter-label">Kelas</label>
                <select name="kelas" class="filter-input w-100">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-input w-100">
                    <option value="">Semua Status</option>
                    @foreach(['hadir','izin','sakit','alpha'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm w-100" style="padding: 9px; border-radius: 8px; font-weight: 600;">
                    <i class="bi bi-search"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ STATISTIK RINGKAS ============ --}}
@php
    $hadirCount = $absensis->where('status', 'hadir')->count();
    $izinCount  = $absensis->where('status', 'izin')->count();
    $sakitCount = $absensis->where('status', 'sakit')->count();
    $alphaCount = $absensis->where('status', 'alpha')->count();
@endphp

<div class="row g-2 mb-3">
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #4338ca, #6366f1);">
            <div class="num">{{ $absensis->total() }}</div>
            <div class="lbl">Total Record</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #10b981, #34d399);">
            <div class="num">{{ $hadirCount }}</div>
            <div class="lbl">Hadir</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #06b6d4, #22d3ee);">
            <div class="num">{{ $izinCount }}</div>
            <div class="lbl">Izin</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
            <div class="num">{{ $sakitCount }}</div>
            <div class="lbl">Sakit</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #ef4444, #f87171);">
            <div class="num">{{ $alphaCount }}</div>
            <div class="lbl">Alpha</div>
        </div>
    </div>
</div>

{{-- ============ TABEL DATA ABSENSI ============ --}}
<div class="card-flat">
    <div class="card-head" style="background: #f8fafc;">
        <h6 style="margin: 0; font-weight: 700;">
            <i class="bi bi-clipboard-data-fill" style="color: #4338ca;"></i>
            Data Absensi
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
        <table class="table-rekap">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Tanggal</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th class="text-center">Masuk</th>
                    <th class="text-center">Foto Masuk</th>
                    <th class="text-center">Pulang</th>
                    <th class="text-center">Foto Pulang</th>
                    <th class="text-center">Status</th>
                    <th>Keterangan</th>
                    <th class="text-center" style="width: 70px;">Aksi</th>
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
                    {{-- FOTO MASUK --}}
                    <td class="text-center">
                        @if($a->foto_masuk)
                            <img src="{{ $a->foto_masuk }}" alt="Foto Masuk" class="rounded border" style="width: 42px; height: 42px; object-fit: cover;">
                        @else
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
                    {{-- FOTO PULANG --}}
                    <td class="text-center">
                        @if($a->foto_pulang)
                            <img src="{{ $a->foto_pulang }}" alt="Foto Pulang" class="rounded border" style="width: 42px; height: 42px; object-fit: cover;">
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
                    <td>
                        @if($a->keterangan)
                            <span style="color: #475569; font-size: 12px;">{{ $a->keterangan }}</span>
                        @else
                            <span style="color: #cbd5e1;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button class="btn-edit" data-bs-toggle="modal"
                                data-bs-target="#modalEdit{{ $a->id }}"
                                title="Edit status">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                    </td>
                </tr>

                {{-- Modal Edit --}}
                <div class="modal fade" id="modalEdit{{ $a->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <form action="{{ route('guru.absensi.update', $a->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="modal-content" style="border-radius: 16px; border: none;">
                                <div class="modal-header" style="border-bottom: 1px solid #f1f5f9;">
                                    <div>
                                        <h6 class="modal-title" style="font-weight: 700; color: #0f172a; margin-bottom: 2px;">
                                            Edit Absensi
                                        </h6>
                                        <div style="font-size: 12px; color: #64748b;">
                                            {{ $a->siswa->nama ?? '' }} — {{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" style="padding: 20px;">
                                    <div class="mb-3">
                                        <label class="filter-label">Status Kehadiran</label>
                                        <select name="status" class="filter-input w-100" required>
                                            @foreach(['hadir','izin','sakit','alpha'] as $s)
                                                <option value="{{ $s }}" {{ $a->status == $s ? 'selected' : '' }}>
                                                    {{ ucfirst($s) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="filter-label">Keterangan</label>
                                        <textarea name="keterangan" class="filter-input w-100"
                                                  rows="3" placeholder="Contoh: Sakit demam, izin acara keluarga, dll...">{{ $a->keterangan }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer" style="border-top: 1px solid #f1f5f9;">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                            data-bs-dismiss="modal"
                                            style="border-radius: 8px; padding: 8px 20px;">
                                        Batal
                                    </button>
                                    <button class="btn btn-primary btn-sm"
                                            style="border-radius: 8px; padding: 8px 20px; font-weight: 600;">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 50px; color: #94a3b8;">
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

{{-- ============ CATATAN PENTING ============ --}}
<div class="catatan-penting mt-4">
    <div class="title">Catatan Penting :</div>
    <ol>
        <li>Maksimal Absen Masuk Di Jam 7:30 WIB.</li>
        <li>Jika Terlambat Harap Konfirmasi Ke Tata Usaha.</li>
        <li>Pengajuan izin/sakit hanya dapat dilakukan orang tua.</li>
    </ol>
</div>

@endsection

@push('styles')
<style>
    /* ============ FILTER ============ */
    .filter-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 6px;
        display: block;
    }
    .filter-input {
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        border: 1px solid #e2e8f0;
        background: #fff;
        outline: none;
        color: #334155;
        transition: 0.15s;
        font-family: inherit;
    }
    .filter-input:focus {
        border-color: #4338ca;
        box-shadow: 0 0 0 3px rgba(67,56,202,0.1);
    }
    textarea.filter-input {
        resize: vertical;
    }
    .filter-reset {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        text-decoration: none;
        font-weight: 500;
        transition: 0.15s;
    }
    .filter-reset:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* ============ STAT CARD ============ */
    .summary-card {
        border-radius: 10px;
        padding: 14px 12px;
        color: #fff;
        text-align: center;
        min-height: 78px;
        transition: transform 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-2px);
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
    .table-rekap {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-rekap th {
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
    .table-rekap td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-rekap tr:last-child td { border-bottom: none; }
    .table-rekap tbody tr:hover { background: #fafbfc; }
    .table-rekap .text-center { text-align: center; }

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

    /* ============ TOMBOL EDIT ============ */
    .btn-edit {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #fef3c7;
        color: #f59e0b;
        border: 1px solid #fde68a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.15s;
        font-size: 13px;
    }
    .btn-edit:hover {
        background: #f59e0b;
        color: #fff;
        border-color: #f59e0b;
        transform: scale(1.08);
    }
</style>
@endpush