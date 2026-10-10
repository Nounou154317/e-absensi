@extends('layouts.app')
@section('title', 'Penugasan — ' . $guru->name)

@section('content')
@include('partials._flash')

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="mb-0">Penugasan Mengajar: {{ $guru->name }}</h6>
            <small class="text-muted">{{ $guru->email }}</small>
        </div>
        <span class="badge bg-primary" id="counter">0 / 3 mapel</span>
    </div>
    <div class="card-body">

        <div class="alert alert-info py-2 small">
            Guru wajib memiliki <strong>minimal 1</strong> dan <strong>maksimal 3</strong> mata pelajaran.
            Untuk tiap mata pelajaran, pilih kelas yang diajar. Menghapus blok = mencabut penugasan mapel itu.
            Satu mata pelajaran di satu kelas hanya dapat diampu satu guru.
        </div>

        @if($kelasList->isEmpty())
            <div class="alert alert-warning py-2 small">Belum ada data kelas (belum ada siswa terdaftar), sehingga kelas belum bisa dipilih.</div>
        @endif
        @if($mapelList->isEmpty())
            <div class="alert alert-warning py-2 small">
                Belum ada mata pelajaran. <a href="{{ route('admin.mapel.create') }}">Tambah mata pelajaran</a> terlebih dahulu.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.penugasan.update', $guru) }}">
            @csrf @method('PUT')

            <div id="wadah"></div>

            <button type="button" class="btn btn-outline-primary btn-sm" id="btnTambah">
                <i class="bi bi-plus-circle"></i> Tambah mata pelajaran
            </button>

            <hr>
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Penugasan</button>
            <a href="{{ route('admin.penugasan.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

{{-- Template satu blok mata pelajaran --}}
<template id="tplBlok">
    <div class="blok border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="judul">Mata pelajaran</strong>
            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus">
                <i class="bi bi-trash"></i> Hapus
            </button>
        </div>

        <select name="penugasan[__IDX__][mapel_id]" class="form-select sel-mapel mb-3" required>
            <option value="">-- Pilih mata pelajaran --</option>
            @foreach($mapelList as $m)
                <option value="{{ $m->id }}">{{ $m->kode }} — {{ $m->nama }}</option>
            @endforeach
        </select>

        <div class="small text-muted mb-1">Kelas yang diajar</div>
        <div class="row g-2">
            @foreach($kelasList as $k)
            <div class="col-6 col-md-4 col-lg-3">
                <label class="form-check kelas-item border rounded px-3 py-2 d-block" style="margin: 0;">
                    <input type="checkbox" class="form-check-input" name="penugasan[__IDX__][kelas][]" value="{{ $k }}">
                    <span class="form-check-label">{{ $k }}</span>
                    <small class="text-danger d-block pakai-ket"></small>
                </label>
            </div>
            @endforeach
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
(function () {
    const MAKS     = {{ \App\Services\PenugasanGuru::MAKS_MAPEL }};
    const TERPAKAI = @json($terpakai);   // {mapel_id: {kelas: namaGuruLain}}
    const AWAL     = @json($blok);       // [{mapel_id, kelas: [...]}]

    const wadah     = document.getElementById('wadah');
    const tpl       = document.getElementById('tplBlok').innerHTML;
    const btnTambah = document.getElementById('btnTambah');
    const counter   = document.getElementById('counter');
    let seq = 0;

    const semuaBlok = () => wadah.querySelectorAll('.blok');

    function tambah(data) {
        if (semuaBlok().length >= MAKS) return;

        const pembungkus = document.createElement('div');
        pembungkus.innerHTML = tpl.replace(/__IDX__/g, seq++).trim();
        const blok = pembungkus.firstElementChild;
        wadah.appendChild(blok);

        const sel = blok.querySelector('.sel-mapel');
        if (data && data.mapel_id) sel.value = String(data.mapel_id);
        if (data && Array.isArray(data.kelas)) {
            blok.querySelectorAll('input[type=checkbox]').forEach(cb => {
                cb.checked = data.kelas.includes(cb.value);
            });
        }

        sel.addEventListener('change', refresh);
        blok.querySelector('.btn-hapus').addEventListener('click', () => {
            if (semuaBlok().length > 1) { blok.remove(); refresh(); }
        });
        refresh();
    }

    function refresh() {
        const dipilih = Array.from(semuaBlok()).map(b => b.querySelector('.sel-mapel').value).filter(Boolean);

        semuaBlok().forEach((b, i) => {
            const sel = b.querySelector('.sel-mapel');
            b.querySelector('.judul').textContent = 'Mata pelajaran ' + (i + 1);

            // Cegah mapel yang sama dipilih dua kali
            sel.querySelectorAll('option').forEach(o => {
                o.disabled = o.value !== '' && o.value !== sel.value && dipilih.includes(o.value);
            });

            // Nonaktifkan kelas yang sudah diampu guru lain untuk mapel ini
            const terpakai = TERPAKAI[sel.value] || {};
            b.querySelectorAll('.kelas-item').forEach(item => {
                const cb  = item.querySelector('input');
                const ket = item.querySelector('.pakai-ket');
                const nama = terpakai[cb.value];
                if (nama) {
                    cb.checked = false;
                    cb.disabled = true;
                    ket.textContent = 'Diampu: ' + nama;
                } else {
                    cb.disabled = false;
                    ket.textContent = '';
                }
            });

            b.querySelector('.btn-hapus').style.visibility = semuaBlok().length > 1 ? 'visible' : 'hidden';
        });

        counter.textContent = semuaBlok().length + ' / ' + MAKS + ' mapel';
        btnTambah.disabled = semuaBlok().length >= MAKS;
    }

    btnTambah.addEventListener('click', () => tambah(null));
    AWAL.forEach(d => tambah(d));
})();
</script>
@endpush