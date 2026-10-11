@extends('layouts.app')
@section('title', 'Dashboard Orang Tua')

@section('content')

<h3 class="page-title">Dashboard Orang Tua</h3>

@include('partials._flash')

{{-- ============ BANNER ============ --}}
<div class="welcome-banner">
    <div>
        <h4>Halo, {{ auth()->user()->name }}</h4>
        <div class="meta">
            Orang tua dari <strong>{{ $anak->nama }}</strong> • Kelas {{ $anak->kelas }}
        </div>
        <span class="badge-tahun">Tahun ajaran {{ $tahunAjaran }}</span>
    </div>
    <div class="banner-right">
        <a href="{{ route('orang_tua.izin') }}" class="btn-ajukan">+ Ajukan Izin / Sakit</a>
    </div>
</div>

@include('orang_tua._anak_picker')

@php
    $persen = $ringkasan['persentase'] ?? 0;
    [$kBg, $kFg] = $ringkasan['warna'];
@endphp

{{-- ============ INFO SISWA | STATUS HARI INI | PERSENTASE ============ --}}
<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="card-flat h-100" style="margin-bottom: 0;">
            <div class="card-head"><h6>Informasi Siswa</h6></div>
            <div class="card-body">
                <div class="info-row"><span>Nama</span><strong>{{ $anak->nama }}</strong></div>
                <div class="info-row"><span>NIS</span><strong>{{ $anak->nis }}</strong></div>
                <div class="info-row"><span>Kelas</span><strong>{{ $anak->kelas }}</strong></div>
                <div class="info-row"><span>Jenis kelamin</span><strong>{{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong></div>
                @if($anak->no_hp)
                <div class="info-row"><span>No. HP</span><strong>{{ $anak->no_hp }}</strong></div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-flat h-100" style="margin-bottom: 0;">
            <div class="card-head">
                <h6>Status Hari Ini</h6>
                <span style="font-size: 12px; color: #64748b;">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <div class="card-body" style="text-align: center; padding: 22px 18px;">
                <span class="badge-status status-{{ $hariIni['key'] }} badge-lg">{{ $hariIni['judul'] }}</span>
                <div style="font-size: 13px; color: #475569; margin-top: 14px; line-height: 1.5;">{{ $hariIni['detail'] }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-flat h-100" style="margin-bottom: 0;">
            <div class="card-head"><h6>Persentase Kehadiran</h6></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="pct-big" style="color: {{ $kFg }};">{{ $persen + 0 }}%</div>
                    <span class="kategori-chip" style="background: {{ $kBg }}; color: {{ $kFg }};">
                        {{ $ringkasan['kategori'] ?? 'Belum ada data' }}
                    </span>
                </div>
                <div class="pct-bar"><div style="width: {{ min(100, $persen) }}%; background: {{ $kFg }};"></div></div>
                <div style="font-size: 12px; color: #64748b;">
                    {{ $ringkasan['hadir'] }} hari hadir dari {{ $ringkasan['hari_efektif'] }} hari efektif sekolah
                </div>
                @if($ringkasan['definisi'])
                <div style="font-size: 12px; color: #475569; margin-top: 8px; line-height: 1.5;">{{ $ringkasan['definisi'] }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ============ RINGKASAN HADIR / IZIN / SAKIT / ALPHA ============ --}}
<div style="font-size: 12px; color: #64748b; margin-bottom: 8px;">Ringkasan tahun ajaran {{ $tahunAjaran }}</div>
<div class="sum-grid">
    <div class="stat-box hadir"><div class="num">{{ $ringkasan['hadir'] }}</div><div class="lbl">Hadir</div></div>
    <div class="stat-box izin"><div class="num">{{ $ringkasan['izin'] }}</div><div class="lbl">Izin</div></div>
    <div class="stat-box sakit"><div class="num">{{ $ringkasan['sakit'] }}</div><div class="lbl">Sakit</div></div>
    <div class="stat-box tidak"><div class="num">{{ $ringkasan['alpha'] }}</div><div class="lbl">Alpha</div></div>
</div>

{{-- ============ RIWAYAT ABSENSI ============ --}}
<div class="card-flat">
    <div class="card-head">
        <h6>Riwayat Absensi — {{ $anak->nama }}</h6>
        <a href="{{ route('orang_tua.kalender') }}" class="nav-btn">Lihat kalender &amp; rekap</a>
    </div>

    <form method="GET" action="{{ route('orang_tua.dashboard') }}" class="filter-bar">
        <div>
            <label>Bulan</label>
            <input type="month" name="bulan" value="{{ $filter['bulan'] }}" class="f-input">
        </div>
        <div>
            <label>Dari tanggal</label>
            <input type="date" name="dari" value="{{ $filter['dari'] }}" class="f-input">
        </div>
        <div>
            <label>Sampai tanggal</label>
            <input type="date" name="sampai" value="{{ $filter['sampai'] }}" class="f-input">
        </div>
        <div>
            <label>Status</label>
            <select name="status" class="f-input">
                <option value="">Semua</option>
                @foreach(['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'] as $v => $l)
                    <option value="{{ $v }}" {{ $filter['status'] === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="actions">
            <button class="btn-x sm">Terapkan</button>
            <a href="{{ route('orang_tua.dashboard') }}" class="btn-x sm outline">Reset</a>
        </div>
    </form>
    <div style="font-size: 11px; color: #94a3b8; padding: 6px 18px 0;">Jika kolom Bulan diisi, rentang tanggal diabaikan.</div>

    <div class="tbl-scroll">
        <table class="table-riwayat">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Masuk</th>
                    <th>Pulang</th>
                    <th>Foto Masuk</th>
                    <th>Foto Pulang</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $r)
                @php
                    $tgl         = \Carbon\Carbon::parse($r->tanggal);
                    $isTerlambat = $r->status === 'hadir' && $r->jam_masuk && $r->jam_masuk > '07:00:00';
                    $statusKey   = $isTerlambat ? 'terlambat' : $r->status;
                    $statusText  = $isTerlambat ? 'Terlambat' : ucfirst($r->status);
                @endphp
                <tr>
                    <td>{{ $tgl->format('d/m/Y') }}</td>
                    <td>{{ $tgl->translatedFormat('l') }}</td>
                    <td>{{ $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '-' }}</td>
                    <td>{{ $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '-' }}</td>
                    
                    {{-- Foto Masuk --}}
                    <td>
                        @if($r->foto_masuk)
                            <img src="{{ $r->foto_masuk }}" class="foto-absen" alt="Foto masuk" loading="lazy"
                                 onclick="showFoto('{{ $r->foto_masuk }}', 'masuk', '{{ $tgl->format('d/m/Y') }}', '{{ $r->jam_masuk }}')">
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>

                    {{-- Foto Pulang --}}
                    <td>
                        @if($r->foto_pulang)
                            <img src="{{ $r->foto_pulang }}" class="foto-absen" alt="Foto pulang" loading="lazy"
                                 onclick="showFoto('{{ $r->foto_pulang }}', 'pulang', '{{ $tgl->format('d/m/Y') }}', '{{ $r->jam_pulang }}')">
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>

                    <td><span class="badge-status status-{{ $statusKey }}">{{ $statusText }}</span></td>
                    <td style="max-width: 260px; font-size: 12px; color: #475569;">
                        @if($isTerlambat)
                            <span style="color: #dc2626; font-style: italic;">Harap tepat waktu</span>
                        @elseif($r->keterangan)
                            {{ \Illuminate\Support\Str::limit($r->keterangan, 90) }}
                            @if($r->pengajuan && $r->pengajuan->catatan_verifikasi)
                                <div style="color: #64748b; margin-top: 2px;">Catatan guru: {{ $r->pengajuan->catatan_verifikasi }}</div>
                            @endif
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; padding: 36px; color: #94a3b8;">Belum ada riwayat absensi untuk filter ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if($riwayat->hasPages())
    <div class="mb-3">{{ $riwayat->links() }}</div>
@endif

{{-- ============ DAFTAR PENGAJUAN IZIN / SAKIT ============ --}}
<div class="card-flat">
    <div class="card-head">
        <h6>Pengajuan Izin &amp; Sakit</h6>
        <a href="{{ route('orang_tua.izin') }}" class="btn-x sm">+ Ajukan</a>
    </div>
    <div class="tbl-scroll">
        <table class="table-riwayat">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Alasan</th>
                    <th>Diajukan</th>
                    <th>Status</th>
                    <th>Catatan Verifikasi</th>
                    <th>Bukti</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $p)
                <tr>
                    <td style="white-space: nowrap;">{{ $p->rentang_tanggal }}</td>
                    <td><span class="badge-status status-{{ $p->jenis }}">{{ ucfirst($p->jenis) }}</span></td>
                    <td style="max-width: 220px; font-size: 12px;">{{ \Illuminate\Support\Str::limit($p->alasan, 80) }}</td>
                    <td style="font-size: 12px; white-space: nowrap;">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                    <td><span class="badge-status status-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                    <td style="max-width: 220px; font-size: 12px; color: #475569;">
                        @if($p->catatan_verifikasi)
                            {{ $p->catatan_verifikasi }}
                            @if($p->verifikator)
                                <div style="color: #94a3b8;">— {{ $p->verifikator->name }}</div>
                            @endif
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    <td style="font-size: 12px; white-space: nowrap;">
                        <a href="{{ route('berkas.pengajuan', [$p, 'foto']) }}" target="_blank">Foto</a>
                        @if($p->surat_dokter)
                            · <a href="{{ route('berkas.pengajuan', [$p, 'surat']) }}" target="_blank">Surat</a>
                        @endif
                    </td>
                    <td>
                        @if($p->status === 'menunggu')
                        <form action="{{ route('orang_tua.izin.batal', $p) }}" method="POST"
                              onsubmit="return confirm('Batalkan pengajuan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-x sm danger">Batalkan</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; padding: 30px; color: #94a3b8;">Belum ada pengajuan izin atau sakit</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="catatan-penting">
    <div class="title">Catatan Penting :</div>
    <ol>
        <li>Persentase kehadiran dihitung dari jumlah hari hadir ÷ {{ $ringkasan['hari_efektif'] }} hari efektif dalam satu tahun ajaran.</li>
        <li>Pengajuan izin/sakit baru tercatat di absensi setelah disetujui guru.</li>
        <li>Pengajuan sakit wajib melampirkan surat dokter.</li>
    </ol>
</div>

@include('orang_tua._foto_modal')

@endsection

@push('styles')
    @include('orang_tua._styles')
@endpush