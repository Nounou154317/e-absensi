@extends('layouts.app')
@section('title', 'Dashboard Siswa')

@section('content')

<h3 class="page-title">Dashboard Siswa</h3>

{{-- ============ BANNER ============ --}}
<div class="welcome-banner">
    <div>
        <h4>Halo, {{ $siswa->nama }}</h4>
        <div class="meta">
            NIS: {{ $siswa->nis }} • Kelas: {{ $siswa->kelas }}
        </div>
        <span class="badge-tahun">Tahun ajaran 2025/2026 • Semester Ganjil</span>
    </div>
    <div class="clock-box">
        <div class="clock live-clock">--:--:--</div>
        <div class="date">{{ now()->translatedFormat('d F Y') }}</div>
    </div>
</div>

{{-- ============ ABSEN + STATISTIK ============ --}}
<div class="row g-3 mb-3">

    {{-- Absen Masuk --}}
    <div class="col-md-3">
        <div class="absen-card masuk">
            <div class="title">Absen Masuk</div>
            <div class="info">Batas absen pulang 7:00 WIB</div>
            @if($absensiHariIni && $absensiHariIni->jam_masuk)
                <button class="absen-btn masuk" disabled>Sudah Absen — {{ $absensiHariIni->jam_masuk }}</button>
            @elseif($absensiHariIni && in_array($absensiHariIni->status, ['izin', 'sakit']))
                <button class="absen-btn masuk" disabled>Tercatat {{ ucfirst($absensiHariIni->status) }}</button>
            @else
                <button class="absen-btn masuk"
                        data-bs-toggle="modal" data-bs-target="#modalMasuk">
                    absen masuk
                </button>
            @endif
        </div>
    </div>

    {{-- Absen Pulang --}}
    <div class="col-md-3">
        <div class="absen-card pulang">
            <div class="title">Absen Pulang</div>
            <div class="info">Aktif dari 15:00 WIB</div>
            @if($absensiHariIni && $absensiHariIni->jam_pulang)
                <button class="absen-btn pulang" disabled>Sudah Absen — {{ $absensiHariIni->jam_pulang }}</button>
            @elseif($absensiHariIni && $absensiHariIni->jam_masuk)
                <button class="absen-btn pulang"
                        data-bs-toggle="modal" data-bs-target="#modalPulang">
                    absen pulang
                </button>
            @else
                <button class="absen-btn pulang" disabled>absen pulang</button>
            @endif
        </div>
    </div>

    {{-- Statistik Kehadiran --}}
    <div class="col-md-6">
        <div class="statistik-card">
            <div class="head">statistik Kehadiran <span class="float-end">Minggu ke-1</span></div>
            <div class="stat-grid">
                <div class="stat-box hadir">
                    <div class="num">{{ $statistik['hadir'] }}</div>
                    <div class="lbl">Hadir</div>
                </div>
                <div class="stat-box terlambat">
                    <div class="num">{{ $statistik['terlambat'] }}</div>
                    <div class="lbl">Terlambat</div>
                </div>
                <div class="stat-box sakit">
                    <div class="num">{{ $statistik['sakit'] }}</div>
                    <div class="lbl">sakit / izin</div>
                </div>
                <div class="stat-box tidak">
                    <div class="num">{{ $statistik['alpha'] }}</div>
                    <div class="lbl">Tidak hadir</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ RIWAYAT + JADWAL ============ --}}
<div class="row g-3">

    {{-- Riwayat Absen --}}
    <div class="col-md-8">
        <div class="card-flat">
            <div class="card-head">
                <h6>Riwayat Absen</h6>
                <div class="filter-group">
                    <select>
                        <option>minggu ke-1</option>
                        <option>minggu ke-2</option>
                        <option>minggu ke-3</option>
                        <option>minggu ke-4</option>
                    </select>
                    <select>
                        @foreach(range(1,12) as $m)
                            <option {{ now()->month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="overflow-x: auto;">
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
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $r)
                    @php $tgl = \Carbon\Carbon::parse($r->tanggal); @endphp
                    <tr>
                        <td>{{ $tgl->format('d/m/Y') }}</td>
                        <td>{{ $tgl->translatedFormat('l') }}</td>
                        <td>{{ $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '-' }}</td>
                        <td>{{ $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '-' }}</td>

                        {{-- Foto Masuk --}}
                        <td>
                            @if($r->foto_masuk)
                                <img src="{{ $r->foto_masuk }}"
                                     class="foto-absen" alt="Foto masuk" loading="lazy"
                                     onclick="showFoto('{{ $r->foto_masuk }}', 'masuk', '{{ $tgl->format('d/m/Y') }}', '{{ $r->jam_masuk }}')">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Foto Pulang --}}
                        <td>
                            @if($r->foto_pulang)
                                <img src="{{ $r->foto_pulang }}"
                                     class="foto-absen" alt="Foto pulang" loading="lazy"
                                     onclick="showFoto('{{ $r->foto_pulang }}', 'pulang', '{{ $tgl->format('d/m/Y') }}', '{{ $r->jam_pulang }}')">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @php
                                $isTerlambat = $r->jam_masuk && $r->jam_masuk > '07:00:00' && $r->status == 'hadir';
                                $statusText = $isTerlambat ? 'Terlambat' : ucfirst($r->status);
                                $statusClass = $isTerlambat ? 'status-terlambat' : 'status-'.$r->status;
                            @endphp
                            <span class="badge-status {{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                        <td>
                            @if($isTerlambat)
                                <span class="note-text">Harap Tepat Waktu</span>
                            @elseif(!$r->jam_masuk)
                                <span class="badge-status status-belum">belum absen</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center; padding:30px; color:#94a3b8;">Belum ada riwayat absensi</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- Jadwal Hari Ini --}}
    <div class="col-md-4">
        <div class="card-flat">
            <div class="card-head">
                <h6>Jadwal Hari Ini</h6>
            </div>
            <div class="jadwal-list">
                <div class="jadwal-item">
                    <div class="jadwal-time">7.30 - 8.30</div>
                    <div class="jadwal-mapel">Matematika</div>
                </div>
                <div class="jadwal-item">
                    <div class="jadwal-time">8.30 - 10.30</div>
                    <div class="jadwal-mapel">Bahasa Indonesia</div>
                </div>
                <div class="jadwal-item">
                    <div class="jadwal-time">10.30 - 11.00</div>
                    <div class="jadwal-mapel">Istirahat</div>
                </div>
                <div class="jadwal-item">
                    <div class="jadwal-time">11.00 - 12.00</div>
                    <div class="jadwal-mapel">Bahasa Inggris</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ CATATAN PENTING ============ --}}
<div class="catatan-penting">
    <div class="title">Catatan Penting :</div>
    <ol>
        <li>Absen Masuk Maksimal Di Jam 7:30 WIB</li>
        <li>Jika Terlambat Harap Konfirmasi Ke Tata Usaha</li>
        <li>Pengajuan izin/sakit hanya dapat dilakukan orang tua</li>
    </ol>
</div>

{{-- ============ MODAL ABSEN MASUK ============ --}}
<div class="modal fade camera-modal" id="modalMasuk" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Foto absen masuk</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <video id="videoMasuk" autoplay playsinline></video>
                <canvas id="canvasMasuk"></canvas>
                <img id="previewMasuk">
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success btn-sm" id="btnAmbilMasuk"><i class="bi bi-camera"></i> Ambil Foto</button>
                <button class="btn btn-primary btn-sm" id="btnKirimMasuk" style="display:none;"><i class="bi bi-send"></i> Kirim</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODAL ABSEN PULANG ============ --}}
<div class="modal fade camera-modal" id="modalPulang" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Foto absen pulang</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <video id="videoPulang" autoplay playsinline></video>
                <canvas id="canvasPulang"></canvas>
                <img id="previewPulang">
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success btn-sm" id="btnAmbilPulang"><i class="bi bi-camera"></i> Ambil Foto</button>
                <button class="btn btn-primary btn-sm" id="btnKirimPulang" style="display:none;"><i class="bi bi-send"></i> Kirim</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODAL LIHAT FOTO ============ --}}
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-body p-4 text-center">
                <h5 id="fotoTitle" style="font-weight: 700; color: #0f172a; font-size: 16px; margin-bottom: 4px;"></h5>
                <p id="fotoSubtitle" style="color: #64748b; font-size: 12px; margin-bottom: 16px;"></p>

                <img id="fotoLarge" src="" alt="Foto Absen"
                     style="width: 100%; border-radius: 12px; display: block;">

                <button class="btn btn-secondary btn-sm mt-3"
                        data-bs-dismiss="modal"
                        style="border-radius: 8px; padding: 8px 24px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .foto-absen {
        width: 44px;
        height: 44px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: 0.2s;
    }
    .foto-absen:hover {
        transform: scale(1.15);
        border-color: #4338ca;
        box-shadow: 0 4px 12px rgba(67,56,202,0.3);
    }
</style>
@endpush

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

// Tampilkan foto absen di modal
function showFoto(src, tipe, tanggal, jam) {
    document.getElementById('fotoLarge').src = src;
    document.getElementById('fotoTitle').textContent = 'Foto Absen ' + tipe.charAt(0).toUpperCase() + tipe.slice(1);
    document.getElementById('fotoSubtitle').textContent = tanggal + ' • ' + (jam ? jam.substring(0, 5) : '-');
    new bootstrap.Modal(document.getElementById('modalFoto')).show();
}

function initCamera(cfg) {
    const video   = document.getElementById(cfg.video);
    const canvas  = document.getElementById(cfg.canvas);
    const preview = document.getElementById(cfg.preview);
    const btnAmbil = document.getElementById(cfg.btnAmbil);
    const btnKirim = document.getElementById(cfg.btnKirim);
    let stream = null, fotoData = null;

    document.getElementById(cfg.modal).addEventListener('shown.bs.modal', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            video.srcObject = stream;
        } catch (e) {
            showToast('Tidak bisa akses kamera: ' + e.message, 'danger');
        }
    });

    document.getElementById(cfg.modal).addEventListener('hidden.bs.modal', () => {
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        video.style.display = 'block';
        preview.style.display = 'none';
        preview.src = '';
        btnAmbil.style.display = 'inline-block';
        btnKirim.style.display = 'none';
        fotoData = null;
    });

    btnAmbil.addEventListener('click', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        fotoData = canvas.toDataURL('image/png');
        preview.src = fotoData;
        preview.style.transform = 'scaleX(-1)';
        preview.style.display = 'block';
        video.style.display = 'none';
        btnAmbil.style.display = 'none';
        btnKirim.style.display = 'inline-block';
    });

    btnKirim.addEventListener('click', () => {
        if (!fotoData) return;
        btnKirim.disabled = true;
        btnKirim.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        fetch(cfg.endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ foto: fotoData })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success', 'Berhasil');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(res.message, 'danger', 'Gagal');
                btnKirim.disabled = false;
                btnKirim.innerHTML = '<i class="bi bi-send"></i> Kirim';
            }
        });
    });
}

initCamera({
    modal: 'modalMasuk', video: 'videoMasuk', canvas: 'canvasMasuk',
    preview: 'previewMasuk', btnAmbil: 'btnAmbilMasuk', btnKirim: 'btnKirimMasuk',
    endpoint: '{{ route("siswa.absen.masuk") }}'
});
initCamera({
    modal: 'modalPulang', video: 'videoPulang', canvas: 'canvasPulang',
    preview: 'previewPulang', btnAmbil: 'btnAmbilPulang', btnKirim: 'btnKirimPulang',
    endpoint: '{{ route("siswa.absen.pulang") }}'
});
</script>
@endpush