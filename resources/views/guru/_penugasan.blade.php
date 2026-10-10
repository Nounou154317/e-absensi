{{-- ============ MATA PELAJARAN YANG DIAMPU ============ --}}
<div class="card-flat mb-3">
    <div class="card-head" style="background: #f8fafc;">
        <h6 style="margin: 0; font-weight: 700;">
            <i class="bi bi-journal-bookmark-fill" style="color: #4338ca;"></i> Mata Pelajaran yang Diampu
        </h6>
        <span class="mp-count">{{ $penugasan['jumlah_mapel'] }} / 3 mapel</span>
    </div>
    <div class="card-body" style="padding: 20px;">
        @if($penugasan['jumlah_mapel'] === 0)
            <div class="alert alert-warning py-2 mb-0" style="font-size: 13px;">
                Anda belum memiliki penugasan mata pelajaran. Silakan hubungi admin untuk penugasan mengajar.
            </div>
        @else
            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="mp-mini"><div class="n">{{ $penugasan['jumlah_mapel'] }}</div><div class="l">Mata pelajaran</div></div>
                </div>
                <div class="col-4">
                    <div class="mp-mini"><div class="n">{{ $penugasan['jumlah_kelas'] }}</div><div class="l">Kelas diajar</div></div>
                </div>
                <div class="col-4">
                    <div class="mp-mini"><div class="n">{{ $penugasan['jumlah_siswa'] }}</div><div class="l">Siswa</div></div>
                </div>
            </div>

            <div class="row g-3">
                @foreach($penugasan['per_mapel'] as $pm)
                <div class="col-md-6 col-xl-4">
                    <div class="mp-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="mp-kode">{{ $pm['mapel']->kode }}</span>
                            <span class="mp-siswa"><i class="bi bi-people"></i> {{ $pm['jumlah_siswa'] }} siswa</span>
                        </div>
                        <div class="mp-nama">{{ $pm['mapel']->nama }}</div>
                        @if($pm['mapel']->keterangan)
                            <div class="mp-ket">{{ $pm['mapel']->keterangan }}</div>
                        @endif
                        <div class="mp-kelas">
                            @foreach($pm['kelas'] as $k)
                                <span class="badge-kelas">{{ $k }}</span>
                            @endforeach
                        </div>
                        <a href="{{ route('guru.nilai.index', ['mapel_id' => $pm['mapel']->id, 'kelas' => $pm['kelas'][0]]) }}"
                           class="mp-link">Input nilai <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .mp-count { font-size: 12px; font-weight: 700; color: #4338ca; background: #ede9fe; padding: 4px 12px; border-radius: 12px; }
    .mp-mini { background: #f8fafc; border-radius: 10px; padding: 12px 14px; text-align: center; }
    .mp-mini .n { font-size: 24px; font-weight: 800; color: #4338ca; line-height: 1; }
    .mp-mini .l { font-size: 11px; color: #64748b; margin-top: 4px; }
    .mp-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; height: 100%; background: #fff; transition: .2s; }
    .mp-card:hover { border-color: #c7d2fe; box-shadow: 0 6px 16px rgba(67,56,202,.08); }
    .mp-kode { background: #4338ca; color: #fff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
    .mp-siswa { font-size: 11px; color: #64748b; }
    .mp-nama { font-size: 15px; font-weight: 700; color: #0f172a; }
    .mp-ket { font-size: 12px; color: #64748b; margin-top: 2px; }
    .mp-kelas { margin: 10px 0 12px; display: flex; flex-wrap: wrap; gap: 6px; }
    .mp-link { font-size: 12px; font-weight: 600; color: #4338ca; text-decoration: none; }
    .mp-link:hover { text-decoration: underline; }
</style>
@endpush

@push('scripts')
<script>
    // Jika admin mengubah penugasan guru ini, dashboard dimuat ulang otomatis (cek tiap 8 detik)
    (function () {
        let hashSekarang = @json($penugasanHash);
        const url = @json(route('guru.penugasan.live'));

        async function cek() {
            if (document.hidden) return;
            try {
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.hash !== hashSekarang) {
                    hashSekarang = data.hash;
                    showToast('Penugasan mengajar Anda diperbarui oleh admin. Memuat ulang...', 'warning', 'Penugasan berubah');
                    setTimeout(() => location.reload(), 1200);
                }
            } catch (e) { /* abaikan, coba lagi nanti */ }
        }
        setInterval(cek, 8000);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) cek(); });
    })();
</script>
@endpush