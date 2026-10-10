@extends('layouts.app')
@section('title', 'Riwayat Nilai')

@section('content')

{{-- ============ HEADER ============ --}}
<h3 class="page-title">Riwayat Nilai</h3>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div style="font-size: 13px; color: #64748b;">
        {{ $siswa->nama }} &nbsp; Kelas : {{ $siswa->kelas }}
    </div>
    <form method="GET">
        <select name="semester" class="filter-select" onchange="this.form.submit()"
                style="padding: 8px 16px; border-radius: 8px; font-size: 13px; border: 1px solid #e2e8f0; background: #fff; outline: none; color: #334155;">
            <option value="2026/2027 Ganjil" {{ $semester == '2026/2027 Ganjil' ? 'selected' : '' }}>
                Semester 1 Ganjil 2026/2027
            </option>
            <option value="2026/2027 Genap" {{ $semester == '2026/2027 Genap' ? 'selected' : '' }}>
                Semester 2 Genap 2026/2027
            </option>
            <option value="2025/2026 Ganjil" {{ $semester == '2025/2026 Ganjil' ? 'selected' : '' }}>
                Semester 1 Ganjil 2025/2026
            </option>
        </select>
    </form>
</div>

{{-- ============ BELUM TERSEDIA: SISWA HANYA MENERIMA HASIL AKHIR ============ --}}
@if(!$rekap['siap'])
<div class="card-flat" style="padding: 40px 20px; text-align: center;">
    <div style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">Nilai belum tersedia</div>
    <div style="font-size: 13px; color: #64748b;">
        @if(!$rekap['lengkap'])
            Nilai semester {{ $semester }} akan ditampilkan setelah guru menyelesaikan penilaian seluruh mata pelajaran.
        @else
            Nilai akhir sedang diproses. Silakan cek kembali nanti.
        @endif
    </div>
</div>
@else

@php
    [$kBg, $kFg] = $absensi['warna'];
    $predikatAkhir = \App\Models\Nilai::predikat($nilaiAkhir);
@endphp

{{-- ============ KARTU HASIL AKHIR ============ --}}
<div class="row g-2 mb-3">
    <div class="col-md-3">
        <div class="summary-card" style="background: #4338ca;">
            <div class="num">{{ $nilaiAkhir + 0 }}</div>
            <div class="lbl">Nilai Akhir &middot; Predikat {{ $predikatAkhir }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card" style="background: #f8fafc; color: #0f172a;">
            <div class="num" style="color: #10b981;">{{ round($tertinggi) }}</div>
            <div class="lbl" style="color: #64748b;">Tertinggi — {{ $mapelTertinggi }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card" style="background: #f8fafc; color: #0f172a;">
            <div class="num" style="color: #ef4444;">{{ $remedial }}</div>
            <div class="lbl" style="color: #64748b;">Perlu remedial</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card" style="background: {{ $kBg }}; color: {{ $kFg }};">
            <div class="num" style="color: {{ $kFg }};">{{ $absensi['persentase'] + 0 }}%</div>
            <div class="lbl" style="color: {{ $kFg }}; opacity: 1;">Kehadiran &middot; {{ $absensi['kategori'] }}</div>
        </div>
    </div>
</div>

{{-- ============ KATEGORI KEHADIRAN ============ --}}
<div class="card-flat mb-3" style="padding: 14px 20px;">
    <div style="font-size: 12px; color: #64748b;">
        Kehadiran {{ $absensi['hadir'] }} dari {{ $absensi['hari_efektif'] }} hari efektif
        <span class="kategori-badge" style="background: {{ $kBg }}; color: {{ $kFg }}; margin-left: 8px;">
            {{ $absensi['kategori'] }}
        </span>
    </div>
    <div style="font-size: 13px; color: #475569; margin-top: 8px; line-height: 1.5;">
        {{ $absensi['definisi'] }}
    </div>
</div>

{{-- ============ CARD GRAFIK NILAI ============ --}}
<div class="card-flat mb-3" style="border: 2px solid #4338ca;">
    <div class="card-head" style="background: #f8fafc;">
        <h6 style="margin: 0; font-weight: 700;">Nilai Akhir per Mata Pelajaran</h6>
        <div style="font-size: 12px; color: #64748b;">
            Garis putus-putus = <strong>KKM (75)</strong>
        </div>
    </div>
    <div class="card-body" style="padding: 22px;">
        <canvas id="chartNilai" height="220"></canvas>
    </div>
</div>

{{-- ============ TABEL NILAI AKHIR ============ --}}
<div class="card-flat">
    <div class="card-head" style="background: #f8fafc; flex-wrap: wrap;">
        <h6 style="margin: 0; font-weight: 700;">Rincian nilai akhir</h6>

        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="{{ route('siswa.nilai', ['semester' => $semester, 'filter' => 'semua']) }}"
               class="filter-tab {{ $filter == 'semua' ? 'active' : '' }}">semua</a>
            <a href="{{ route('siswa.nilai', ['semester' => $semester, 'filter' => 'tuntas']) }}"
               class="filter-tab {{ $filter == 'tuntas' ? 'active' : '' }}">Tuntas</a>
            <a href="{{ route('siswa.nilai', ['semester' => $semester, 'filter' => 'remedial']) }}"
               class="filter-tab {{ $filter == 'remedial' ? 'active' : '' }}">Perlu Remedial</a>

            <button onclick="window.print()" class="btn-cetak">
                Cetak
            </button>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="table-nilai">
            <thead>
                <tr>
                    <th style="text-align: left; padding-left: 20px;">MATA PELAJARAN</th>
                    <th class="text-center">NILAI AKHIR</th>
                    <th class="text-center">PREDIKAT</th>
                    <th class="text-center">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($nilais as $n)
                <tr>
                    <td style="text-align: left; padding-left: 20px; font-weight: 500; color: #1e293b;">
                        {{ $n['mapel'] }}
                    </td>
                    <td class="text-center">
                        <strong class="{{ $n['nilai_akhir'] < 75 ? 'nilai-rendah' : '' }}">
                            {{ round($n['nilai_akhir']) }}
                        </strong>
                    </td>
                    <td class="text-center">
                        <span class="predikat">{{ \App\Models\Nilai::predikat($n['nilai_akhir']) }}</span>
                    </td>
                    <td class="text-center">
                        @if($n['nilai_akhir'] >= 75)
                            <span class="status-badge status-tuntas">Tuntas</span>
                        @else
                            <span class="status-badge status-remedial">Remedial</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                        Belum ada data nilai untuk filter ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

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
    .kategori-badge {
        display: inline-block;
        padding: 5px 14px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 700;
    }

    /* ============ KARTU RINGKASAN ============ */
    .summary-card {
        border-radius: 10px;
        padding: 16px 20px;
        color: #fff;
        min-height: 90px;
    }
    .summary-card .num {
        font-size: 32px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 6px;
    }
    .summary-card .lbl {
        font-size: 11px;
        opacity: 0.9;
        line-height: 1.3;
    }

    /* ============ FILTER TAB ============ */
    .filter-tab {
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: #fff;
        color: #64748b;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        transition: all 0.15s;
    }
    .filter-tab:hover {
        background: #f8fafc;
        color: #4338ca;
        border-color: #c7d2fe;
    }
    .filter-tab.active {
        background: #4338ca;
        color: #fff;
        border-color: #4338ca;
    }

    /* ============ TOMBOL CETAK ============ */
    .btn-cetak {
        padding: 6px 18px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        background: #fff;
        color: #4338ca;
        border: 1.5px solid #4338ca;
        cursor: pointer;
        transition: all 0.15s;
    }
    .btn-cetak:hover {
        background: #4338ca;
        color: #fff;
    }

    /* ============ TABEL NILAI ============ */
    .table-nilai {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-nilai th {
        padding: 12px 12px;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .table-nilai td {
        padding: 14px 12px;
        border-bottom: 1px solid #f1f5f9;
        color: #475569;
    }
    .table-nilai tr:last-child td {
        border-bottom: none;
    }
    .table-nilai .text-center { text-align: center; }

    .nilai-rendah {
        color: #ef4444 !important;
        font-weight: 600;
    }

    .predikat {
        display: inline-block;
        font-weight: 700;
        color: #4338ca;
        font-size: 14px;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    .status-tuntas   { background: #d1fae5; color: #065f46; }
    .status-remedial { background: #fee2e2; color: #991b1b; }

    /* ============ PRINT ============ */
    @media print {
        .sidebar, .topbar, .filter-tab, .btn-cetak, .catatan-penting {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .card-flat {
            break-inside: avoid;
        }
    }
</style>
@endpush

@push('scripts')
@if($rekap['siap'])
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Data dari Laravel (hanya nilai akhir per mapel)
const labels    = @json($semuaNilai->pluck('mapel')->values());
const dataNilai = @json($semuaNilai->pluck('nilai_akhir')->values());
const KKM       = {{ \App\Http\Controllers\NilaiController::KKM }};

// Warna dinamis: tuntas = biru/ungu, remedial = merah
const colors = dataNilai.map(v => v >= KKM ? '#4338ca' : '#ef4444');

// Custom plugin: garis putus-putus vertikal di KKM
const kkmLinePlugin = {
    id: 'kkmLine',
    afterDatasetsDraw(chart) {
        const { ctx, chartArea: { top, bottom }, scales: { x } } = chart;
        const xPos = x.getPixelForValue(KKM);

        ctx.save();
        ctx.strokeStyle = '#ef4444';
        ctx.lineWidth = 1.5;
        ctx.setLineDash([6, 4]);
        ctx.beginPath();
        ctx.moveTo(xPos, top);
        ctx.lineTo(xPos, bottom);
        ctx.stroke();

        // Label "75" di bawah garis
        ctx.fillStyle = '#ef4444';
        ctx.font = '11px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('75', xPos, bottom + 15);

        ctx.restore();
    }
};

new Chart(document.getElementById('chartNilai'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            data: dataNilai,
            backgroundColor: colors,
            borderRadius: 20,
            barThickness: 12,
        }]
    },
    options: {
        indexAxis: 'y',           // bikin horizontal
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { right: 30, bottom: 20 } },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => 'Nilai: ' + ctx.parsed.x
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                max: 100,
                grid: { color: '#f1f5f9' },
                ticks: { color: '#94a3b8', font: { size: 11 } }
            },
            y: {
                grid: { display: false },
                ticks: { color: '#334155', font: { size: 11 } }
            }
        }
    },
    plugins: [kkmLinePlugin]
});
</script>
@endif
@endpush