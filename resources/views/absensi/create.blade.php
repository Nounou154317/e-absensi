@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-white"><h5 class="mb-0">Absen Masuk</h5></div>
    <div class="card-body">
        <form action="{{ route('absensi.store') }}" method="POST" id="formAbsen">
            @csrf
            <div class="mb-3">
                <label class="form-label">Pilih Siswa</label>
                <select name="siswa_id" class="form-select" required>
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($siswas as $s)
                        <option value="{{ $s->id }}">{{ $s->nis }} - {{ $s->nama }} ({{ $s->kelas }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Foto Selfie</label>
                <div>
                    <video id="video" autoplay playsinline></video>
                    <canvas id="canvas" style="display:none;"></canvas>
                    <img id="preview" style="display:none; max-width:480px;" class="mt-2 rounded"/>
                </div>
                <input type="hidden" name="foto" id="foto">
                <div class="mt-2">
                    <button type="button" class="btn btn-secondary" id="btnAmbil">
                        <i class="bi bi-camera"></i> Ambil Foto
                    </button>
                    <button type="button" class="btn btn-warning" id="btnUlang" style="display:none;">
                        <i class="bi bi-arrow-clockwise"></i> Ulangi
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
                <i class="bi bi-send"></i> Kirim Absensi
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const preview = document.getElementById('preview');
const fotoInput = document.getElementById('foto');
const btnAmbil = document.getElementById('btnAmbil');
const btnUlang = document.getElementById('btnUlang');
const btnSubmit = document.getElementById('btnSubmit');

navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => video.srcObject = stream)
    .catch(err => alert('Tidak dapat mengakses kamera: ' + err));

btnAmbil.addEventListener('click', () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    const dataURL = canvas.toDataURL('image/png');
    fotoInput.value = dataURL;

    preview.src = dataURL;
    preview.style.display = 'block';
    video.style.display = 'none';
    btnAmbil.style.display = 'none';
    btnUlang.style.display = 'inline-block';
    btnSubmit.disabled = false;
});

btnUlang.addEventListener('click', () => {
    fotoInput.value = '';
    preview.style.display = 'none';
    video.style.display = 'block';
    btnAmbil.style.display = 'inline-block';
    btnUlang.style.display = 'none';
    btnSubmit.disabled = true;
});
</script>
@endpush