@extends('layouts.app')
@section('title', 'Ajukan Izin / Sakit')

@section('content')

<h3 class="page-title">Ajukan Izin / Sakit</h3>

@include('partials._flash')

<div class="row g-3">
    <div class="col-lg-8">
        <form action="{{ route('orang_tua.izin.store') }}" method="POST" enctype="multipart/form-data"
              class="card-flat" id="formIzin">
            @csrf
            <div class="card-head"><h6>Formulir Pengajuan</h6></div>
            <div class="card-body" style="padding: 20px;">

                {{-- Anak --}}
                <div class="mb-3">
                    <label class="lbl-f">Anak</label>
                    <select name="siswa_id" class="f-input" required>
                        @foreach($anakList as $a)
                            <option value="{{ $a->id }}" {{ (int) old('siswa_id', $anak->id) === $a->id ? 'selected' : '' }}>
                                {{ $a->nama }} — {{ $a->kelas }} (NIS {{ $a->nis }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="lbl-f">Tanggal ketidakhadiran</label>
                        <input type="date" name="tanggal_mulai" class="f-input"
                               value="{{ old('tanggal_mulai', now()->toDateString()) }}"
                               min="{{ $minTanggal }}" max="{{ $maxTanggal }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="lbl-f">Sampai tanggal <small style="font-weight: 400;">(opsional, untuk lebih dari 1 hari)</small></label>
                        <input type="date" name="tanggal_selesai" class="f-input"
                               value="{{ old('tanggal_selesai') }}"
                               min="{{ $minTanggal }}" max="{{ $maxTanggal }}">
                    </div>
                </div>

                {{-- Jenis --}}
                <div class="mb-3">
                    <label class="lbl-f">Jenis ketidakhadiran</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <label class="pilih-jenis">
                            <input type="radio" name="jenis" value="izin" {{ old('jenis') === 'izin' ? 'checked' : '' }} required>
                            <span>Izin</span>
                        </label>
                        <label class="pilih-jenis">
                            <input type="radio" name="jenis" value="sakit" {{ old('jenis') === 'sakit' ? 'checked' : '' }}>
                            <span>Sakit</span>
                        </label>
                    </div>
                </div>

                {{-- Alasan --}}
                <div class="mb-3">
                    <label class="lbl-f">Alasan / keterangan</label>
                    <textarea name="alasan" rows="3" class="f-input" minlength="10" maxlength="1000"
                              placeholder="Jelaskan alasan ketidakhadiran (minimal 10 karakter)" required>{{ old('alasan') }}</textarea>
                </div>

                {{-- Foto bukti --}}
                <div class="mb-3">
                    <label class="lbl-f">Foto bukti pendukung <span style="color: #ef4444;">*</span></label>
                    <input type="file" name="foto_bukti" id="foto_bukti" class="f-input"
                           accept="image/png,image/jpeg" required>
                    <div class="mt-2 d-flex gap-2 align-items-center flex-wrap">
                        <button type="button" class="btn-x sm outline" id="btnKamera">Ambil dengan kamera</button>
                        <small style="color: #64748b;">atau pilih file di atas (JPG/PNG, maks. 5 MB). Di ponsel, dialog unggah juga menawarkan kamera.</small>
                    </div>
                    <img id="previewFoto" alt="Pratinjau foto bukti"
                         style="display: none; margin-top: 10px; max-width: 220px; border-radius: 10px; border: 1px solid #e2e8f0;">
                </div>

                {{-- Surat dokter (khusus sakit) --}}
                <div class="mb-3" id="wrapSurat" style="display: none;">
                    <label class="lbl-f">Surat dokter <span style="color: #ef4444;">* wajib untuk sakit</span></label>
                    <input type="file" name="surat_dokter" id="surat_dokter" class="f-input"
                           accept=".jpg,.jpeg,.png,.pdf,image/png,image/jpeg,application/pdf">
                    <small style="color: #64748b;">Format JPG, JPEG, PNG, atau PDF, maks. 5 MB.</small>
                </div>

                <hr>
                <button class="btn-x">Kirim Pengajuan</button>
                <a href="{{ route('orang_tua.dashboard') }}" class="btn-x outline">Batal</a>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card-flat">
            <div class="card-head"><h6>Ketentuan</h6></div>
            <div class="card-body" style="padding: 16px 18px; font-size: 13px; color: #475569; line-height: 1.6;">
                <ul style="padding-left: 18px; margin: 0;">
                    <li>Foto bukti <strong>wajib</strong> untuk izin maupun sakit.</li>
                    <li>Pengajuan sakit <strong>wajib</strong> melampirkan surat dokter.</li>
                    <li>Pengajuan diperiksa guru. Anda dapat melihat status dan catatan guru di dashboard.</li>
                    <li>Hanya hari sekolah (Senin–Jumat) yang dicatat.</li>
                    <li>Bisa mengajukan sampai {{ \App\Http\Controllers\OrangTuaController::MAKS_HARI_LALU }} hari ke belakang dan {{ \App\Http\Controllers\OrangTuaController::MAKS_HARI_DEPAN }} hari ke depan, maksimal {{ \App\Http\Controllers\OrangTuaController::MAKS_RENTANG }} hari per pengajuan.</li>
                    <li>Pengajuan yang masih menunggu dapat dibatalkan dari dashboard.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODAL KAMERA ============ --}}
<div class="modal fade" id="modalKamera" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 12px;">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Ambil foto bukti</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <video id="videoBukti" autoplay playsinline muted
                       style="width: 100%; border-radius: 8px; background: #000;"></video>
                <canvas id="canvasBukti" style="display: none;"></canvas>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success btn-sm" id="btnJepret">Ambil Foto</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
    @include('orang_tua._styles')
<style>
    .lbl-f { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
    .pilih-jenis { cursor: pointer; margin: 0; }
    .pilih-jenis input { display: none; }
    .pilih-jenis span {
        display: inline-block; padding: 9px 28px; border-radius: 10px; font-size: 13px; font-weight: 600;
        border: 1.5px solid #e2e8f0; color: #475569; background: #fff;
    }
    .pilih-jenis input:checked + span { background: #4338ca; border-color: #4338ca; color: #fff; }
</style>
@endpush

@push('scripts')
<script>
    const MAKS_BYTES = 5 * 1024 * 1024;
    const inputFoto  = document.getElementById('foto_bukti');
    const inputSurat = document.getElementById('surat_dokter');
    const wrapSurat  = document.getElementById('wrapSurat');
    const preview    = document.getElementById('previewFoto');

    // ---- Surat dokter hanya muncul & wajib untuk "sakit" ----
    function toggleSurat() {
        const jenis = document.querySelector('input[name="jenis"]:checked');
        const sakit = jenis && jenis.value === 'sakit';
        wrapSurat.style.display = sakit ? 'block' : 'none';
        inputSurat.required = sakit;
        if (!sakit) inputSurat.value = '';
    }
    document.querySelectorAll('input[name="jenis"]').forEach(r => r.addEventListener('change', toggleSurat));
    toggleSurat();

    // ---- Validasi ukuran + pratinjau ----
    function cekUkuran(input) {
        const f = input.files[0];
        if (f && f.size > MAKS_BYTES) {
            showToast('Ukuran berkas maksimal 5 MB.', 'danger', 'Berkas terlalu besar');
            input.value = '';
            return false;
        }
        return true;
    }
    function tampilkanPreview(file) {
        if (!file || !file.type.startsWith('image/')) { preview.style.display = 'none'; return; }
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
    inputFoto.addEventListener('change', () => {
        if (cekUkuran(inputFoto)) tampilkanPreview(inputFoto.files[0]);
        else preview.style.display = 'none';
    });
    inputSurat.addEventListener('change', () => cekUkuran(inputSurat));

    // ---- Kamera ----
    const modalEl = document.getElementById('modalKamera');
    const video   = document.getElementById('videoBukti');
    const canvas  = document.getElementById('canvasBukti');
    let stream = null;

    function hentikanKamera() {
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        video.srcObject = null;
    }

    document.getElementById('btnKamera').addEventListener('click', () => {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showToast('Kamera tidak tersedia di browser ini. Silakan unggah file.', 'warning', 'Kamera');
            return;
        }
        new bootstrap.Modal(modalEl).show();
    });

    modalEl.addEventListener('shown.bs.modal', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } } });
            video.srcObject = stream;
        } catch (e) {
            showToast('Tidak bisa mengakses kamera: ' + e.message, 'danger', 'Kamera');
        }
    });
    modalEl.addEventListener('hidden.bs.modal', hentikanKamera);

    document.getElementById('btnJepret').addEventListener('click', () => {
        if (!video.videoWidth) return;
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        canvas.toBlob(blob => {
            const file = new File([blob], 'bukti-kamera-' + Date.now() + '.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(file);
            inputFoto.files = dt.files;          // masuk ke field foto_bukti pada form
            tampilkanPreview(file);
            bootstrap.Modal.getInstance(modalEl).hide();
        }, 'image/jpeg', 0.9);
    });
</script>
@endpush