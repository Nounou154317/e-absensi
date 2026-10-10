@extends('layouts.app')
@section('title', 'Input Nilai')

@section('content')

{{-- ============ HEADER ============ --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h3 class="page-title" style="margin: 0;">Input Nilai Siswa</h3>
    <a href="{{ route('guru.nilai.akhir', ['kelas' => $kelas, 'semester' => $semester]) }}"
       style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; background: #4338ca; color: #fff; text-decoration: none;">
        Rekap Nilai Akhir
    </a>
</div>
<div style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
    Pilih kelas & mata pelajaran untuk menginput nilai
</div>

{{-- ============ FILTER ============ --}}
<div class="card-flat mb-3">
    <div class="card-head" style="background: #f8fafc;">
        <h6 style="margin: 0; font-weight: 700;">
            <i class="bi bi-funnel-fill" style="color: #4338ca;"></i> Pilih Kelas & Mapel
        </h6>
    </div>
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="filter-label">Kelas</label>
                <select name="kelas" class="filter-input w-100" onchange="this.form.submit()">
                    @foreach($kelasList as $k)
                        <option value="{{ $k }}" {{ $kelas == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="filter-label">Mata Pelajaran</label>
                <select name="mapel_id" class="filter-input w-100" onchange="this.form.submit()">
                    @foreach($mapelList as $m)
                        <option value="{{ $m->id }}" {{ $mapelId == $m->id ? 'selected' : '' }}>
                            {{ $m->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="filter-label">Semester</label>
                <select name="semester" class="filter-input w-100" onchange="this.form.submit()">
                    <option value="2026/2027 Ganjil" {{ $semester == '2026/2027 Ganjil' ? 'selected' : '' }}>
                        2026/2027 Ganjil
                    </option>
                    <option value="2026/2027 Genap" {{ $semester == '2026/2027 Genap' ? 'selected' : '' }}>
                        2026/2027 Genap
                    </option>
                    <option value="2025/2026 Ganjil" {{ $semester == '2025/2026 Ganjil' ? 'selected' : '' }}>
                        2025/2026 Ganjil
                    </option>
                </select>
            </div>
        </form>
    </div>
</div>

{{-- ============ STATISTIK KELAS INI ============ --}}
@php
    $totalSiswa = $siswas->count();
    $sudahDinilai = $siswas->filter(fn($s) => $s->nilais->count() > 0)->count();
    $belumDinilai = $totalSiswa - $sudahDinilai;

    // Rata-rata nilai kelas (nilai akhir dihitung ulang termasuk nilai absensi)
    $allNilai = $siswas->flatMap(function ($s) use ($absensiMap) {
        $nAbs = $absensiMap[$s->id]['nilai_absensi'] ?? null;
        if ($nAbs === null) return [];
        return $s->nilais->map(fn ($n) => \App\Models\Nilai::hitungAkhir($n->tugas, $n->uts, $n->uas, $nAbs));
    });
    $rataKelas = $allNilai->count() > 0 ? round($allNilai->avg(), 1) : 0;

    // Tuntas vs remedial
    $tuntas   = $allNilai->filter(fn($v) => $v >= 75)->count();
    $remedial = $allNilai->filter(fn($v) => $v < 75)->count();
@endphp

<div class="row g-2 mb-3">
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #4338ca, #6366f1);">
            <div class="num">{{ $totalSiswa }}</div>
            <div class="lbl">Total Siswa</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #10b981, #34d399);">
            <div class="num">{{ $sudahDinilai }}</div>
            <div class="lbl">Sudah Dinilai</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
            <div class="num">{{ $belumDinilai }}</div>
            <div class="lbl">Belum Dinilai</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #06b6d4, #22d3ee);">
            <div class="num">{{ $rataKelas }}</div>
            <div class="lbl">Rata-rata Kelas</div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #ef4444, #f87171);">
            <div class="num">{{ $remedial }}</div>
            <div class="lbl">Perlu Remedial</div>
        </div>
    </div>
</div>

{{-- ============ TABEL SISWA + FORM BULK ============ --}}
<form action="{{ route('guru.nilai.bulk') }}" method="POST" id="formBulk">
    @csrf
    <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
    <input type="hidden" name="semester" value="{{ $semester }}">

    <div class="card-flat">
        <div class="card-head" style="background: #f8fafc;">
            <h6 style="margin: 0; font-weight: 700;">
                <i class="bi bi-clipboard-data-fill" style="color: #4338ca;"></i>
                {{ $mapelTerpilih->nama ?? '-' }}
                <span style="font-weight: 400; color: #64748b; font-size: 12px;">
                    — Kelas {{ $kelas }}
                </span>
            </h6>
            <div style="font-size: 12px; color: #64748b;">
                KKM: <strong>75</strong> | Rumus: 25% Tugas + 30% UTS + 35% UAS + 10% Absensi
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="table-nilai">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th class="text-center" style="width: 110px;">Tugas (25%)</th>
                        <th class="text-center" style="width: 110px;">UTS (30%)</th>
                        <th class="text-center" style="width: 110px;">UAS (35%)</th>
                        <th class="text-center" style="width: 150px;">Absensi (10%)</th>
                        <th class="text-center" style="width: 100px;">Nilai Akhir</th>
                        <th class="text-center" style="width: 80px;">Predikat</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswas as $i => $s)
                    @php
                        $n = $s->nilais->first();
                        $tugas = $n->tugas ?? 0;
                        $uts   = $n->uts ?? 0;
                        $uas   = $n->uas ?? 0;
                        $abs   = $absensiMap[$s->id];
                        $nAbs  = $abs['nilai_absensi'];
                        [$kBg, $kFg] = $abs['warna'];
                        // Nilai akhir dihitung ulang dengan nilai absensi terbaru
                        $akhir = ($n && $nAbs !== null)
                                    ? \App\Models\Nilai::hitungAkhir($tugas, $uts, $uas, $nAbs)
                                    : 0;
                    @endphp
                    <tr data-siswa="{{ $s->id }}" data-absensi="{{ $nAbs ?? '' }}">
                        <td style="color: #94a3b8;">{{ $i + 1 }}</td>
                        <td style="font-family: monospace; color: #475569;">{{ $s->nis }}</td>
                        <td>
                            <div style="font-weight: 500; color: #1e293b;">{{ $s->nama }}</div>
                        </td>
                        <td class="text-center">
                            <input type="number" step="0.01" min="0" max="100"
                                   name="nilai[{{ $s->id }}][tugas]"
                                   class="nilai-input"
                                   data-siswa="{{ $s->id }}"
                                   value="{{ $tugas ?: '' }}"
                                   placeholder="0"
                                   oninput="hitungNilai({{ $s->id }})">
                        </td>
                        <td class="text-center">
                            <input type="number" step="0.01" min="0" max="100"
                                   name="nilai[{{ $s->id }}][uts]"
                                   class="nilai-input"
                                   data-siswa="{{ $s->id }}"
                                   value="{{ $uts ?: '' }}"
                                   placeholder="0"
                                   oninput="hitungNilai({{ $s->id }})">
                        </td>
                        <td class="text-center">
                            <input type="number" step="0.01" min="0" max="100"
                                   name="nilai[{{ $s->id }}][uas]"
                                   class="nilai-input"
                                   data-siswa="{{ $s->id }}"
                                   value="{{ $uas ?: '' }}"
                                   placeholder="0"
                                   oninput="hitungNilai({{ $s->id }})">
                        </td>
                        <td class="text-center">
                            @if($nAbs !== null)
                                <div style="font-weight: 700; color: #1e293b;">{{ $nAbs }}</div>
                                <div style="font-size: 11px; color: #64748b;">
                                    {{ $abs['hadir'] }}/{{ $abs['hari_efektif'] }} hari &middot; {{ $abs['persentase'] }}%
                                </div>
                                <span style="display:inline-block; margin-top:3px; padding:2px 10px; border-radius:10px; font-size:10px; font-weight:700; background: {{ $kBg }}; color: {{ $kFg }};">
                                    {{ $abs['kategori'] }}
                                </span>
                            @else
                                <span style="font-size: 11px; color: #94a3b8;">Belum ada data</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <strong id="akhir-{{ $s->id }}" class="nilai-akhir"
                                    style="color: {{ $akhir >= 75 ? '#4338ca' : '#ef4444' }};">
                                {{ $akhir ?: '-' }}
                            </strong>
                        </td>
                        <td class="text-center">
                            <span id="predikat-{{ $s->id }}" class="predikat-badge" style="display: {{ $akhir ? 'inline-block' : 'none' }};">
                                {{ $akhir ? ($akhir >= 90 ? 'A' : ($akhir >= 80 ? 'B' : ($akhir >= 70 ? 'C' : 'D'))) : '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span id="status-{{ $s->id }}" class="status-badge" style="display: {{ $akhir ? 'inline-block' : 'none' }};">
                                @if($akhir >= 75)
                                    <span class="status-tuntas">Tuntas</span>
                                @elseif($akhir > 0)
                                    <span class="status-remedial">Remedial</span>
                                @endif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 50px; color: #94a3b8;">
                            <i class="bi bi-inbox" style="font-size: 40px; display: block; margin-bottom: 10px; opacity: .5;"></i>
                            Tidak ada siswa di kelas ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswas->count())
        <div class="card-footer" style="padding: 14px 20px; border-top: 1px solid #f1f5f9; background: #f8fafc;">
            <div class="d-flex justify-content-between align-items-center">
                <div style="font-size: 12px; color: #64748b;">
                    <i class="bi bi-info-circle"></i>
                    Isi nilai 0-100. Nilai kosong akan dilewati.
                </div>
                <button type="submit" class="btn btn-primary btn-sm"
                        style="border-radius: 8px; padding: 8px 24px; font-weight: 600;">
                    <i class="bi bi-save"></i> Simpan Semua Nilai
                </button>
            </div>
        </div>
        @endif
    </div>
</form>

{{-- ============ CATATAN PENTING ============ --}}
<div class="catatan-penting mt-4">
    <div class="title">Catatan Penting :</div>
    <ol>
        <li>Nilai Akhir = 25% Tugas + 30% UTS + 35% UAS + 10% Absensi.</li>
        <li>Persentase kehadiran = (jumlah hari hadir ÷ {{ \App\Models\Absensi::HARI_EFEKTIF }} hari efektif) × 100%.
            Sangat Baik 95–100% (100) &bull; Baik 90–94% (90) &bull; Cukup 85–89% (80) &bull; Kurang 75–84% (70) &bull; Buruk &lt;75% (60).</li>
        <li>Siswa hanya menerima hasil akhir; rincian dan rumus hanya terlihat oleh guru.</li>
        <li>Siswa dengan nilai akhir di bawah <strong>75</strong> wajib remedial.</li>
        <li>Nilai yang kosong akan dilewati saat menyimpan.</li>
    </ol>
</div>

@endsection

@push('styles')
<style>
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
    }
    .filter-input:focus {
        border-color: #4338ca;
        box-shadow: 0 0 0 3px rgba(67,56,202,0.1);
    }

    .summary-card {
        border-radius: 10px;
        padding: 14px 12px;
        color: #fff;
        text-align: center;
        min-height: 78px;
        transition: transform 0.2s;
    }
    .summary-card:hover { transform: translateY(-2px); }
    .summary-card .num {
        font-size: 24px;
        font-weight: 700;
        line-height: 1;
    }
    .summary-card .lbl {
        font-size: 11px;
        margin-top: 6px;
        opacity: 0.95;
        font-weight: 500;
    }

    .table-nilai {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-nilai th {
        padding: 12px 14px;
        background: #f8fafc;
        text-align: left;
        color: #64748b;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e2e8f0;
    }
    .table-nilai td {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-nilai tr:last-child td { border-bottom: none; }
    .table-nilai tbody tr:hover { background: #fafbfc; }
    .table-nilai .text-center { text-align: center; }

    .nilai-input {
        width: 80px;
        padding: 7px 10px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        font-size: 13px;
        text-align: center;
        outline: none;
        transition: 0.15s;
        font-weight: 600;
        color: #1e293b;
    }
    .nilai-input:focus {
        border-color: #4338ca;
        box-shadow: 0 0 0 3px rgba(67,56,202,0.1);
        background: #fafbff;
    }
    .nilai-input::-webkit-outer-spin-button,
    .nilai-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .nilai-input[type=number] { -moz-appearance: textfield; }

    .nilai-akhir {
        font-size: 16px;
        font-weight: 700;
    }

    .predikat-badge {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        border-radius: 50%;
        background: #ede9fe;
        color: #4338ca;
        font-weight: 700;
        font-size: 13px;
        text-align: center;
    }

    .status-badge { font-size: 11px; }
    .status-tuntas,
    .status-remedial {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-weight: 600;
    }
    .status-tuntas   { background: #d1fae5; color: #065f46; }
    .status-remedial { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@push('scripts')
<script>
function hitungNilai(siswaId) {
    const inputs = document.querySelectorAll(`.nilai-input[data-siswa="${siswaId}"]`);
    const tugas = parseFloat(inputs[0].value) || 0;
    const uts   = parseFloat(inputs[1].value) || 0;
    const uas   = parseFloat(inputs[2].value) || 0;

    const row = document.querySelector(`tr[data-siswa="${siswaId}"]`);
    const absRaw = row ? row.dataset.absensi : '';
    const el = document.getElementById('akhir-' + siswaId);
    const predikatEl = document.getElementById('predikat-' + siswaId);
    const statusEl = document.getElementById('status-' + siswaId);

    // Kalau semua nilai 0/kosong, atau data absensi belum ada
    if ((tugas === 0 && uts === 0 && uas === 0) || absRaw === '') {
        el.textContent = '-';
        el.style.color = '#94a3b8';
        predikatEl.style.display = 'none';
        statusEl.style.display = 'none';
        return;
    }

    // Tugas 30% + UTS 25% + UAS 35% + Absensi 10%
    const akhir = (tugas * 0.25) + (uts * 0.30) + (uas * 0.35) + (parseFloat(absRaw) * 0.10);
    el.textContent = akhir.toFixed(2);
    el.style.color = akhir >= 75 ? '#4338ca' : '#ef4444';

    // Predikat
    let predikat = 'D';
    if (akhir >= 90) predikat = 'A';
    else if (akhir >= 80) predikat = 'B';
    else if (akhir >= 70) predikat = 'C';

    predikatEl.textContent = predikat;
    predikatEl.style.display = 'inline-block';
    predikatEl.style.background = akhir >= 75 ? '#ede9fe' : '#fee2e2';
    predikatEl.style.color = akhir >= 75 ? '#4338ca' : '#991b1b';

    // Status
    statusEl.innerHTML = akhir >= 75
        ? '<span class="status-tuntas">Tuntas</span>'
        : '<span class="status-remedial">Remedial</span>';
    statusEl.style.display = 'inline-block';
}

// Auto-save with Enter key
document.querySelectorAll('.nilai-input').forEach(input => {
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const inputs = Array.from(document.querySelectorAll('.nilai-input'));
            const idx = inputs.indexOf(input);
            if (idx < inputs.length - 1) inputs[idx + 1].focus();
        }
    });
});
</script>
@endpush