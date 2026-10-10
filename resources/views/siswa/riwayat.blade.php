@extends('layouts.app')
@section('title', 'Riwayat Absen')

@section('content')

{{-- Header halaman --}}
<h3 class="page-title">Riwayat Absensi</h3>
<div style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
    {{ $siswa->nama }} • Kelas : {{ $siswa->kelas }} • TA 2026/2027 • Semester Ganjil
</div>

{{-- ============ STATISTIK 5 KOTAK ============ --}}
<div class="row g-2 mb-3">
    <div class="col">
        <div class="stat-box-big" style="background: #d1fae5;">
            <div class="num" style="color: #065f46;">{{ $statistik['hadir'] }}</div>
            <div class="lbl" style="color: #065f46;">Hadir</div>
        </div>
    </div>
    <div class="col">
        <div class="stat-box-big" style="background: #fef3c7;">
            <div class="num" style="color: #92400e;">{{ $statistik['terlambat'] }}</div>
            <div class="lbl" style="color: #92400e;">Terlambat</div>
        </div>
    </div>
    <div class="col">
        <div class="stat-box-big" style="background: #dbeafe;">
            <div class="num" style="color: #1e40af;">{{ $statistik['izin'] }}</div>
            <div class="lbl" style="color: #1e40af;">Izin</div>
        </div>
    </div>
    <div class="col">
        <div class="stat-box-big" style="background: #e9d5ff;">
            <div class="num" style="color: #6b21a8;">{{ $statistik['sakit'] }}</div>
            <div class="lbl" style="color: #6b21a8;">Sakit</div>
        </div>
    </div>
    <div class="col">
        <div class="stat-box-big" style="background: #fee2e2;">
            <div class="num" style="color: #991b1b;">{{ $statistik['alpha'] }}</div>
            <div class="lbl" style="color: #991b1b;">Alpha</div>
        </div>
    </div>
</div>

{{-- ============ PROGRESS BAR KEHADIRAN ============ --}}
<div class="mb-3">
    <div style="font-size: 12px; color: #64748b; margin-bottom: 6px;">
        Tingkat Kehadiran <strong>{{ $persentase }}%</strong> ({{ $statistik['hadir'] }} dari {{ $totalHari }} Hari sekolah)
    </div>
    <div style="height: 8px; background: #e2e8f0; border-radius: 20px; overflow: hidden;">
        <div style="height: 100%; width: {{ $persentase }}%; background: #10b981; border-radius: 20px; transition: width 0.6s;"></div>
    </div>
</div>

{{-- ============ FILTER TAB ============ --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="{{ route('siswa.riwayat') }}"
    class="filter-tab {{ !request('status') ? 'active' : '' }}">Semua</a>
    <a href="{{ route('siswa.riwayat', ['status' => 'hadir']) }}"
    class="filter-tab {{ request('status') == 'hadir' ? 'active' : '' }}">Hadir</a>
    <a href="{{ route('siswa.riwayat', ['status' => 'terlambat']) }}"
    class="filter-tab {{ request('status') == 'terlambat' ? 'active' : '' }}">Terlambat</a>
    <a href="{{ route('siswa.riwayat', ['status' => 'izin']) }}"
    class="filter-tab {{ request('status') == 'izin' ? 'active' : '' }}">Izin</a>
    <a href="{{ route('siswa.riwayat', ['status' => 'sakit']) }}"
    class="filter-tab {{ request('status') == 'sakit' ? 'active' : '' }}">Sakit</a>
    <a href="{{ route('siswa.riwayat', ['status' => 'alpha']) }}" class="filter-tab {{ request('status') == 'alpha' ? 'active' : '' }}">Alpha</a>

    <select class="filter-select ms-auto" onchange="location = this.value;">
        <option value="{{ route('siswa.riwayat') }}">Semua Minggu</option>
        <option value="{{ route('siswa.riwayat', ['minggu' => 1]) }}">Minggu ke-1</option>
        <option value="{{ route('siswa.riwayat', ['minggu' => 2]) }}">Minggu ke-2</option>
        <option value="{{ route('siswa.riwayat', ['minggu' => 3]) }}">Minggu ke-3</option>
        <option value="{{ route('siswa.riwayat', ['minggu' => 4]) }}">Minggu ke-4</option>
    </select>
</div>

{{-- ============ TABEL RIWAYAT ============ --}}
<div class="card-flat">
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
            <tr>
                <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('l') }}</td>
                <td>{{ $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '-' }}</td>
                <td>{{ $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '-' }}</td>
                <td>
                    @if($r->foto_masuk)
                        <img src="{{ asset('storage/' . $r->foto_masuk) }}"
                             class="foto-absen" alt="Foto masuk"
                             onclick="showFoto('{{ asset('storage/' . $r->foto_masuk) }}', 'masuk', '{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}', '{{ $r->jam_masuk }}')">
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </td>
                <td>
                    @if($r->foto_pulang)
                        <img src="{{ asset('storage/' . $r->foto_pulang) }}"
                             class="foto-absen" alt="Foto pulang"
                             onclick="showFoto('{{ asset('storage/' . $r->foto_pulang) }}', 'pulang', '{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}', '{{ $r->jam_pulang }}')">
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </td>
                <td>
                    @php
                        $isTerlambat = $r->jam_masuk && $r->jam_masuk > '07:00:00' && $r->status == 'hadir';
                        if ($isTerlambat) {
                            $statusText = 'Terlambat';
                            $statusClass = 'status-terlambat';
                        } else {
                            $statusText = ucfirst($r->status);
                            $statusClass = 'status-' . $r->status;
                        }
                    @endphp
                    <span class="badge-status {{ $statusClass }}">{{ $statusText }}</span>
                </td>
                <td>
                    @if($isTerlambat)
                        <span style="color: #dc2626; font-style: italic; font-size: 11px;">Harap Tepat Waktu</span>
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                    Belum ada riwayat absensi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ============ CATATAN PENTING ============ --}}
<div class="catatan-penting mt-4">
    <div class="title">Catatan Penting :</div>
    <ol>
        <li>Maksimal Absen Masuk Di Jam 7:30 WIB.</li>
        <li>Jika Terlambat Harap Konfirmasi Ke Tata Usaha.</li>
        <li>Pengajuan izin/sakit hanya dapat dilakukan orang tua.</li>
    </ol>
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
    .stat-box-big {
        border-radius: 10px;
        padding: 16px 8px;
        text-align: center;
        min-height: 85px;
    }
    .stat-box-big .num {
        font-size: 30px;
        font-weight: 700;
        line-height: 1;
    }
    .stat-box-big .lbl {
        font-size: 12px;
        margin-top: 6px;
        font-weight: 500;
    }

    .filter-tab {
        padding: 6px 16px;
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

    .filter-select {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        background: #fff;
        color: #475569;
        border: 1px solid #e2e8f0;
        outline: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // Tampilkan foto absen di modal
    function showFoto(src, tipe, tanggal, jam) {
        document.getElementById('fotoLarge').src = src;
        document.getElementById('fotoTitle').textContent = 'Foto Absen ' + tipe.charAt(0).toUpperCase() + tipe.slice(1);
        document.getElementById('fotoSubtitle').textContent = tanggal + ' • ' + (jam ? jam.substring(0, 5) : '-');
        new bootstrap.Modal(document.getElementById('modalFoto')).show();
    }
</script>
@endpush