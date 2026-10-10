@extends('layouts.app')
@section('title', 'Rekap Nilai Akhir')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h3 class="page-title" style="margin: 0;">Rekap Nilai Akhir</h3>
    <a href="{{ route('guru.nilai.index', ['kelas' => $kelas, 'semester' => $semester]) }}"
       style="font-size: 13px; color: #4338ca; text-decoration: none; font-weight: 600;">&larr; Kembali ke Input Nilai</a>
</div>
<div style="font-size: 13px; color: #64748b; margin: 6px 0 16px;">
    Nilai akhir = Tugas &times; 30% + UTS &times; 25% + UAS &times; 35% + Absensi &times; 10%
    (kehadiran dihitung dari {{ \App\Models\Absensi::HARI_EFEKTIF }} hari efektif).
    Siswa hanya menerima hasil akhir, dan baru bisa melihatnya jika status <strong>Lengkap</strong> serta data absensi tersedia.
</div>

<div class="card-flat mb-3">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="filter-label">Kelas</label>
                <select name="kelas" class="filter-input w-100" onchange="this.form.submit()">
                    @foreach($kelasList as $k)
                        <option value="{{ $k }}" {{ $kelas == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="filter-label">Semester</label>
                <select name="semester" class="filter-input w-100" onchange="this.form.submit()">
                    @foreach(['2026/2027 Ganjil', '2026/2027 Genap', '2025/2026 Ganjil'] as $sm)
                        <option value="{{ $sm }}" {{ $semester == $sm ? 'selected' : '' }}>{{ $sm }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card-flat">
    <div style="overflow-x: auto;">
        <table class="table-akhir">
            <thead>
                <tr>
                    <th>No</th>
                    <th style="text-align: left;">Nama Siswa</th>
                    <th>Mapel Terisi</th>
                    <th>Tugas</th>
                    <th>UTS</th>
                    <th>UAS</th>
                    <th>Hadir / {{ \App\Models\Absensi::HARI_EFEKTIF }}</th>
                    <th>Kehadiran</th>
                    <th>Kategori</th>
                    <th>Nilai Absensi</th>
                    <th>Nilai Akhir</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap as $i => $r)
                @php
                    $d = $r['data'];
                    $a = $d['absensi'];
                    [$kBg, $kFg] = $a['warna'];
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="text-align: left; font-weight: 500; color: #1e293b;">
                        {{ $r['siswa']->nama }}
                        <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">{{ $r['siswa']->nis }}</div>
                    </td>
                    <td>{{ $d['terisi'] }} / {{ $d['total_mapel'] }}</td>
                    <td>{{ $d['rata_tugas'] !== null ? $d['rata_tugas'] + 0 : '-' }}</td>
                    <td>{{ $d['rata_uts'] !== null ? $d['rata_uts'] + 0 : '-' }}</td>
                    <td>{{ $d['rata_uas'] !== null ? $d['rata_uas'] + 0 : '-' }}</td>
                    <td>{{ $a['hadir'] }}</td>
                    <td>{{ $a['persentase'] !== null ? $a['persentase'] . '%' : '-' }}</td>
                    <td>
                        <span class="badge-k" style="background: {{ $kBg }}; color: {{ $kFg }};">{{ $a['kategori'] ?? '-' }}</span>
                    </td>
                    <td>{{ $a['nilai_absensi'] ?? '-' }}</td>
                    <td><strong style="color: #4338ca;">{{ $d['nilai_akhir'] !== null ? $d['nilai_akhir'] + 0 : '-' }}</strong></td>
                    <td>
                        @if($d['siap'])
                            <span class="badge-k" style="background: #d1fae5; color: #065f46;">Dikirim ke siswa</span>
                        @elseif($d['lengkap'])
                            <span class="badge-k" style="background: #fef3c7; color: #92400e;">Data absensi kosong</span>
                        @else
                            <span class="badge-k" style="background: #fef3c7; color: #92400e;">Belum lengkap</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" style="text-align: center; padding: 40px; color: #94a3b8;">Tidak ada siswa di kelas ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('styles')
<style>
    .table-akhir { width: 100%; border-collapse: collapse; font-size: 13px; text-align: center; }
    .table-akhir th { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; }
    .table-akhir td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; color: #475569; }
    .badge-k { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; }
</style>
@endpush