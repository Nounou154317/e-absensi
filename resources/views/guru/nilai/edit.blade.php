@extends('layouts.app')
@section('title', 'Input Nilai — ' . $siswa->nama)

@section('content')

@php
    $nAbs = $absensi['nilai_absensi'];
    [$kBg, $kFg] = $absensi['warna'];
@endphp

<h3 class="page-title">Input Nilai</h3>

{{-- Info Siswa --}}
<div class="card-flat mb-3">
    <div class="card-body" style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <div style="font-weight: 700; color: #0f172a; font-size: 15px;">
                {{ $siswa->nama }}
            </div>
            <div style="font-size: 12px; color: #64748b;">
                NIS: {{ $siswa->nis }} • Kelas: {{ $siswa->kelas }}
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 12px; color: #64748b;">Mata Pelajaran</div>
            <div style="font-weight: 700; color: #4338ca;">{{ $mapel->nama }}</div>
        </div>
    </div>
</div>

<form action="{{ route('guru.nilai.update', $siswa->id) }}" method="POST">
    @csrf @method('PUT')
    <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
    <input type="hidden" name="semester" value="{{ $semester }}">

    <div class="card-flat">
        <div class="card-body" style="padding: 24px;">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="filter-label">Tugas (30%)</label>
                    <input type="number" step="0.01" min="0" max="100"
                           name="tugas" id="tugas" class="nilai-input-lg"
                           value="{{ $nilai->tugas ?? 0 }}"
                           oninput="hitung()" required>
                </div>
                <div class="col-md-4">
                    <label class="filter-label">UTS (25%)</label>
                    <input type="number" step="0.01" min="0" max="100"
                           name="uts" id="uts" class="nilai-input-lg"
                           value="{{ $nilai->uts ?? 0 }}"
                           oninput="hitung()" required>
                </div>
                <div class="col-md-4">
                    <label class="filter-label">UAS (35%)</label>
                    <input type="number" step="0.01" min="0" max="100"
                           name="uas" id="uas" class="nilai-input-lg"
                           value="{{ $nilai->uas ?? 0 }}"
                           oninput="hitung()" required>
                </div>
            </div>

            {{-- Nilai absensi siswa (bobot 10%) --}}
            <div class="mt-4 p-3" style="background: #f8fafc; border-radius: 10px; font-size: 13px; color: #475569;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 6px;">Absensi (10%)</div>
                @if($nAbs !== null)
                    Hadir <strong>{{ $absensi['hadir'] }}</strong> dari {{ $absensi['hari_efektif'] }} hari efektif =
                    <strong>{{ $absensi['persentase'] }}%</strong>
                    <span style="display:inline-block; margin: 0 6px; padding:2px 12px; border-radius:10px; font-size:11px; font-weight:700; background: {{ $kBg }}; color: {{ $kFg }};">{{ $absensi['kategori'] }}</span>
                    &rarr; Nilai Absensi <strong>{{ $nAbs }}</strong>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;">{{ $absensi['definisi'] }}</div>
                @else
                    <span style="color: #92400e;">Belum ada data absensi siswa ini, nilai akhir belum dapat dihitung.</span>
                @endif
            </div>

            <div class="text-center mt-3 p-3" style="background: #f8fafc; border-radius: 10px;">
                <div style="font-size: 12px; color: #64748b;">Nilai Akhir</div>
                <div id="previewAkhir" style="font-size: 36px; font-weight: 800; color: #4338ca;">
                    -
                </div>
                <div id="previewStatus" style="font-size: 12px;"></div>
            </div>
        </div>

        <div class="card-footer" style="padding: 14px 20px; border-top: 1px solid #f1f5f9; background: #f8fafc;">
            <button class="btn btn-primary btn-sm" style="border-radius: 8px; padding: 8px 24px; font-weight: 600;">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('guru.nilai.index', ['kelas' => $siswa->kelas, 'mapel_id' => $mapel->id, 'semester' => $semester]) }}"
               class="btn btn-secondary btn-sm" style="border-radius: 8px; padding: 8px 24px;">
                Kembali
            </a>
        </div>
    </div>
</form>

@endsection

@push('styles')
<style>
    .filter-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
    }
    .nilai-input-lg {
        width: 100%;
        padding: 14px 16px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        font-size: 18px;
        text-align: center;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: 0.15s;
    }
    .nilai-input-lg:focus {
        border-color: #4338ca;
        box-shadow: 0 0 0 4px rgba(67,56,202,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
const NILAI_ABSENSI = @json($nAbs);

function hitung() {
    const tugas = parseFloat(document.getElementById('tugas').value) || 0;
    const uts   = parseFloat(document.getElementById('uts').value) || 0;
    const uas   = parseFloat(document.getElementById('uas').value) || 0;
    const el = document.getElementById('previewAkhir');
    const statusEl = document.getElementById('previewStatus');

    if (NILAI_ABSENSI === null) {
        el.textContent = '-';
        el.style.color = '#94a3b8';
        statusEl.innerHTML = '';
        return;
    }

    // Tugas 30% + UTS 25% + UAS 35% + Absensi 10%
    const akhir = (tugas * 0.30) + (uts * 0.25) + (uas * 0.35) + (NILAI_ABSENSI * 0.10);

    el.textContent = akhir.toFixed(2);
    el.style.color = akhir >= 75 ? '#4338ca' : '#ef4444';

    statusEl.innerHTML = akhir >= 75
        ? '<span style="background:#d1fae5;color:#065f46;padding:4px 14px;border-radius:12px;font-weight:600;">Tuntas</span>'
        : '<span style="background:#fee2e2;color:#991b1b;padding:4px 14px;border-radius:12px;font-weight:600;">Remedial</span>';
}
hitung();
</script>
@endpush