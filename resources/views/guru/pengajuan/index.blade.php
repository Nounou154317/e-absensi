@extends('layouts.app')
@section('title', 'Pengajuan Izin & Sakit')

@section('content')

<h3 class="page-title">Pengajuan Izin &amp; Sakit dari Orang Tua</h3>

@include('partials._flash')

@php
    $tab = [
        'menunggu'  => ['Menunggu', $jumlah['menunggu'] ?? 0],
        'disetujui' => ['Disetujui', $jumlah['disetujui'] ?? 0],
        'ditolak'   => ['Ditolak', $jumlah['ditolak'] ?? 0],
        'semua'     => ['Semua', array_sum($jumlah->all())],
    ];
@endphp

{{-- ============ TAB STATUS + FILTER ============ --}}
<div class="card-flat">
    <div class="card-body p-pad" style="padding: 14px 18px;">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($tab as $key => [$label, $n])
                <a href="{{ route('guru.pengajuan.index', array_filter(['status' => $key, 'kelas' => $kelas, 'q' => $q])) }}"
                   class="anak-chip {{ $status === $key ? 'active' : '' }}">
                    {{ $label }} <small>({{ $n }})</small>
                </a>
            @endforeach
        </div>
        <form method="GET" class="row g-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ $q }}" class="f-input" placeholder="Cari nama atau NIS siswa...">
            </div>
            <div class="col-md-4">
                <select name="kelas" class="f-input" onchange="this.form.submit()">
                    <option value="">Semua kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k }}" {{ $kelas === $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn-x sm flex-fill">Cari</button>
                <a href="{{ route('guru.pengajuan.index') }}" class="btn-x sm outline">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- ============ DAFTAR PENGAJUAN ============ --}}
@forelse($pengajuans as $p)
<div class="card-flat">
    <div class="card-head" style="flex-wrap: wrap; gap: 8px;">
        <div>
            <h6 style="margin: 0;">{{ $p->siswa->nama }}</h6>
            <div style="font-size: 12px; color: #64748b;">NIS {{ $p->siswa->nis }} • Kelas {{ $p->siswa->kelas }}</div>
        </div>
        <span class="badge-status status-{{ $p->status }} badge-lg">{{ ucfirst($p->status) }}</span>
    </div>

    <div class="card-body" style="padding: 16px 18px;">
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="info-row"><span>Orang tua pengaju</span><strong>{{ $p->ortu->name ?? '-' }}</strong></div>
                <div class="info-row">
                    <span>Jenis</span>
                    <strong><span class="badge-status status-{{ $p->jenis }}">{{ ucfirst($p->jenis) }}</span></strong>
                </div>
                <div class="info-row">
                    <span>Tanggal</span>
                    <strong>{{ $p->rentang_tanggal }} <small style="color: #64748b; font-weight: 400;">({{ $p->jumlahHariSekolah() }} hari sekolah)</small></strong>
                </div>
                <div class="info-row"><span>Waktu pengajuan</span><strong>{{ $p->created_at->format('d/m/Y H:i') }}</strong></div>
                <div style="margin-top: 10px; font-size: 13px;">
                    <div style="color: #64748b; font-size: 12px; margin-bottom: 4px;">Alasan</div>
                    <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; color: #334155; white-space: pre-line;">{{ $p->alasan }}</div>
                </div>
            </div>

            <div class="col-lg-5">
                <div style="color: #64748b; font-size: 12px; margin-bottom: 6px;">Foto bukti</div>
                <img src="{{ route('berkas.pengajuan', [$p, 'foto']) }}" alt="Foto bukti" class="foto-bukti"
                     onclick="lihatBukti(this.src)">

                <div style="color: #64748b; font-size: 12px; margin: 12px 0 6px;">Surat dokter</div>
                @if($p->surat_dokter)
                    <a href="{{ route('berkas.pengajuan', [$p, 'surat']) }}" target="_blank" class="btn-x sm outline">Lihat surat dokter</a>
                @else
                    <span style="font-size: 12px; color: #94a3b8;">{{ $p->jenis === 'sakit' ? 'Tidak dilampirkan' : 'Tidak diperlukan (izin)' }}</span>
                @endif
            </div>
        </div>

        <hr>

        @if($p->status === 'menunggu')
            <form method="POST" class="form-verifikasi">
                @csrf
                <label class="lbl-f">Catatan pemeriksaan
                    <small style="font-weight: 400; color: #94a3b8;">(wajib diisi jika menolak)</small>
                </label>
                <textarea name="catatan" rows="2" class="f-input" maxlength="500"
                          placeholder="Contoh: bukti sudah sesuai / foto tidak jelas, mohon unggah ulang"></textarea>
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    <button type="submit" class="btn-x success"
                            formaction="{{ route('guru.pengajuan.setujui', $p) }}"
                            onclick="return confirm('Setujui pengajuan ini? Absensi anak akan dicatat sebagai {{ $p->jenis }}.')">
                        Setujui
                    </button>
                    <button type="submit" class="btn-x danger"
                            formaction="{{ route('guru.pengajuan.tolak', $p) }}"
                            onclick="return cekTolak(this)">
                        Tolak
                    </button>
                </div>
            </form>
        @else
            <div style="font-size: 13px; color: #475569;">
                <strong>Catatan pemeriksaan:</strong> {{ $p->catatan_verifikasi ?: '-' }}
                <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">
                    {{ ucfirst($p->status) }} oleh {{ $p->verifikator->name ?? '-' }}
                    @if($p->diverifikasi_at) pada {{ $p->diverifikasi_at->format('d/m/Y H:i') }} @endif
                </div>
            </div>
        @endif
    </div>
</div>
@empty
<div class="card-flat" style="padding: 40px 20px; text-align: center; color: #94a3b8;">
    Tidak ada pengajuan untuk filter ini
</div>
@endforelse

@if($pengajuans->hasPages())
    <div class="mb-3">{{ $pengajuans->links() }}</div>
@endif

{{-- ============ MODAL BUKTI ============ --}}
<div class="modal fade" id="modalBukti" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: none; border-radius: 14px;">
            <div class="modal-body p-3 text-center">
                <img id="buktiBesar" src="" alt="Foto bukti" style="max-width: 100%; max-height: 75vh; border-radius: 10px;">
                <div class="mt-3"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
    @include('orang_tua._styles')
<style>
    .lbl-f { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
    .foto-bukti {
        width: 100%; max-width: 260px; max-height: 200px; object-fit: cover; cursor: zoom-in;
        border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc;
    }
</style>
@endpush

@push('scripts')
<script>
    function lihatBukti(src) {
        document.getElementById('buktiBesar').src = src;
        new bootstrap.Modal(document.getElementById('modalBukti')).show();
    }
    function cekTolak(btn) {
        const catatan = btn.closest('form').querySelector('textarea[name="catatan"]');
        if (catatan.value.trim().length < 5) {
            alert('Isi alasan penolakan pada kolom catatan pemeriksaan (minimal 5 karakter).');
            catatan.focus();
            return false;
        }
        return confirm('Tolak pengajuan ini?');
    }
</script>
@endpush