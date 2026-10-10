<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrangTuaController extends Controller
{
    const MAKS_HARI_LALU  = 3;    // boleh mengajukan sampai 3 hari ke belakang
    const MAKS_HARI_DEPAN = 30;   // dan sampai 30 hari ke depan
    const MAKS_RENTANG    = 14;   // maksimal 14 hari per pengajuan
    const BATAS_TERLAMBAT = '07:00:00';

    // =====================================================
    //  DASHBOARD
    // =====================================================
    public function dashboard(Request $request)
    {
        [$anakList, $anak] = $this->anakTerpilih($request);
        if (!$anak) return view('orang_tua.kosong');

        $semester    = Absensi::semesterSekarang();
        $tahunAjaran = explode(' ', $semester)[0];
        $ringkasan   = Absensi::penilaian($anak->id, $semester);
        $hariIni     = $this->statusHariIni($anak);

        // ---- filter riwayat: bulan ATAU rentang tanggal, plus status ----
        $bulan  = $this->bulanValid($request->get('bulan'));
        $dari   = $this->tanggalValid($request->get('dari'));
        $sampai = $this->tanggalValid($request->get('sampai'));
        $status = $request->get('status');

        $query = Absensi::with('pengajuan')->where('siswa_id', $anak->id);

        if ($bulan) {
            $query->whereBetween('tanggal', [
                $bulan->toDateString(),
                $bulan->copy()->endOfMonth()->toDateString(),
            ]);
        } else {
            if ($dari)   $query->where('tanggal', '>=', $dari);
            if ($sampai) $query->where('tanggal', '<=', $sampai);
        }

        if ($status === 'terlambat') {
            $query->where('status', 'hadir')->whereNotNull('jam_masuk')
                  ->where('jam_masuk', '>', self::BATAS_TERLAMBAT);
        } elseif (in_array($status, ['hadir', 'izin', 'sakit', 'alpha'])) {
            $query->where('status', $status);
        }

        $riwayat = $query->orderByDesc('tanggal')->paginate(10)->withQueryString();

        $pengajuans = PengajuanIzin::with('verifikator')
            ->where('siswa_id', $anak->id)->latest()->limit(10)->get();

        $filter = [
            'bulan'  => $bulan ? $bulan->format('Y-m') : '',
            'dari'   => $dari,
            'sampai' => $sampai,
            'status' => $status,
        ];

        return view('orang_tua.dashboard', compact(
            'anakList', 'anak', 'tahunAjaran', 'ringkasan', 'hariIni',
            'riwayat', 'pengajuans', 'filter'
        ));
    }

    // =====================================================
    //  KALENDER & REKAP BULANAN
    // =====================================================
    public function kalender(Request $request)
    {
        [$anakList, $anak] = $this->anakTerpilih($request);
        if (!$anak) return view('orang_tua.kosong');

        $awal  = $this->bulanValid($request->get('bulan')) ?? Carbon::today()->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();
        $bulan = $awal->format('Y-m');

        // Tahun ajaran yang memuat bulan ini (Juli – Juni)
        $tahunMulai  = $awal->month >= 7 ? $awal->year : $awal->year - 1;
        $semesterTA  = $tahunMulai . '/' . ($tahunMulai + 1) . ' Ganjil';
        $rentangTA   = Absensi::rentangTahunAjaran($semesterTA);
        $ringkasanTA = Absensi::penilaian($anak->id, $semesterTA);

        $recordsTA = Absensi::with('pengajuan')
            ->where('siswa_id', $anak->id)
            ->whereBetween('tanggal', $rentangTA)
            ->get();

        // ---- rekap per bulan (12 bulan tahun ajaran) ----
        $perBulan = [];
        for ($i = 0; $i < 12; $i++) {
            $m = Carbon::create($tahunMulai, 7, 1)->addMonths($i);
            $perBulan[$m->format('Y-m')] = [
                'label' => $m->translatedFormat('F Y'),
                'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0,
            ];
        }
        foreach ($recordsTA as $r) {
            $k = substr($r->tanggal, 0, 7);
            if (isset($perBulan[$k][$r->status])) {
                $perBulan[$k][$r->status]++;
            }
        }
        foreach ($perBulan as $k => $row) {
            $tercatat = $row['hadir'] + $row['izin'] + $row['sakit'] + $row['alpha'];
            $perBulan[$k]['tercatat'] = $tercatat;
            $perBulan[$k]['persen']   = $tercatat > 0 ? round($row['hadir'] / $tercatat * 100, 1) : null;
        }
        $ringkasanBulan = $perBulan[$bulan];

        // ---- data bulan terpilih ----
        $bulanRecords = $recordsTA->filter(fn ($r) => substr($r->tanggal, 0, 7) === $bulan)
                                  ->sortByDesc('tanggal')->values();
        $byDate = $bulanRecords->keyBy(fn ($r) => substr($r->tanggal, 0, 10));

        // Tanggal yang sedang menunggu verifikasi
        $menungguTgl = [];
        $pending = PengajuanIzin::where('siswa_id', $anak->id)->where('status', 'menunggu')
            ->whereDate('tanggal_mulai', '<=', $akhir->toDateString())
            ->whereDate('tanggal_selesai', '>=', $awal->toDateString())->get();
        foreach ($pending as $p) {
            foreach (PengajuanIzin::hariSekolah($p->tanggal_mulai, $p->tanggal_selesai) as $d) {
                $menungguTgl[$d->toDateString()] = true;
            }
        }

        // ---- grid kalender (Senin–Minggu) ----
        $cells  = [];
        $cursor = $awal->copy()->startOfWeek(Carbon::MONDAY);
        $last   = $akhir->copy()->endOfWeek(Carbon::SUNDAY);
        $today  = Carbon::today();

        while ($cursor->lte($last)) {
            $tgl     = $cursor->toDateString();
            $inMonth = $cursor->month === $awal->month;
            $rec     = $byDate->get($tgl);
            $key     = null;
            $tip     = '';

            if ($inMonth) {
                if ($rec) {
                    if ($rec->status === 'hadir') {
                        $terlambat = $rec->jam_masuk && $rec->jam_masuk > self::BATAS_TERLAMBAT;
                        $key = $terlambat ? 'terlambat' : 'hadir';
                        $tip = 'Masuk ' . ($rec->jam_masuk ? substr($rec->jam_masuk, 0, 5) : '-')
                             . ' • Pulang ' . ($rec->jam_pulang ? substr($rec->jam_pulang, 0, 5) : '-');
                    } else {
                        $key = $rec->status;
                        $tip = $rec->keterangan ?? '';
                    }
                } elseif ($cursor->isWeekend()) {
                    $key = 'libur';
                } elseif (isset($menungguTgl[$tgl])) {
                    $key = 'menunggu';
                    $tip = 'Pengajuan menunggu verifikasi guru';
                } elseif ($cursor->gt($today)) {
                    $key = 'mendatang';
                } else {
                    $key = 'kosong';
                    $tip = 'Tidak ada data absensi';
                }
            }

            $cells[] = [
                'day'      => $cursor->day,
                'in_month' => $inMonth,
                'key'      => $key,
                'tip'      => $tip,
                'today'    => $cursor->isSameDay($today),
            ];
            $cursor->addDay();
        }
        $weeks = array_chunk($cells, 7);

        $judulBulan  = $awal->translatedFormat('F Y');
        $prev        = $awal->copy()->subMonth()->format('Y-m');
        $next        = $awal->copy()->addMonth()->format('Y-m');
        $tahunAjaran = explode(' ', $semesterTA)[0];

        return view('orang_tua.kalender', compact(
            'anakList', 'anak', 'weeks', 'judulBulan', 'prev', 'next', 'bulan',
            'bulanRecords', 'ringkasanBulan', 'perBulan', 'ringkasanTA', 'tahunAjaran'
        ));
    }

    // =====================================================
    //  PENGAJUAN IZIN / SAKIT
    // =====================================================
    public function izinForm(Request $request)
    {
        [$anakList, $anak] = $this->anakTerpilih($request);
        if (!$anak) return view('orang_tua.kosong');

        $minTanggal = Carbon::today()->subDays(self::MAKS_HARI_LALU)->toDateString();
        $maxTanggal = Carbon::today()->addDays(self::MAKS_HARI_DEPAN)->toDateString();

        return view('orang_tua.izin', compact('anakList', 'anak', 'minTanggal', 'maxTanggal'));
    }

    public function izinStore(Request $request)
    {
        $request->validate([
            'siswa_id'        => 'required|integer',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jenis'           => 'required|in:izin,sakit',
            'alasan'          => 'required|string|min:10|max:1000',
            'foto_bukti'      => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'surat_dokter'    => 'required_if:jenis,sakit|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'tanggal_mulai.required'   => 'Tanggal ketidakhadiran wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'jenis.required'           => 'Pilih jenis ketidakhadiran (izin atau sakit).',
            'alasan.required'          => 'Alasan wajib diisi.',
            'alasan.min'               => 'Alasan minimal 10 karakter agar jelas bagi guru.',
            'foto_bukti.required'      => 'Foto bukti wajib dilampirkan.',
            'foto_bukti.mimes'         => 'Foto bukti harus berformat JPG, JPEG, atau PNG.',
            'foto_bukti.max'           => 'Ukuran foto bukti maksimal 5 MB.',
            'surat_dokter.required_if' => 'Surat dokter wajib dilampirkan untuk pengajuan sakit.',
            'surat_dokter.mimes'       => 'Surat dokter harus berformat JPG, JPEG, PNG, atau PDF.',
            'surat_dokter.max'         => 'Ukuran surat dokter maksimal 5 MB.',
        ]);

        // Hanya boleh untuk anak yang terhubung dengan akun ini (selain itu → 404)
        $anak = auth()->user()->anak()->findOrFail($request->siswa_id);

        $hariIni = Carbon::today();
        $mulai   = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $selesai = $request->filled('tanggal_selesai')
                    ? Carbon::parse($request->tanggal_selesai)->startOfDay()
                    : $mulai->copy();

        if ($mulai->lt($hariIni->copy()->subDays(self::MAKS_HARI_LALU))) {
            return $this->gagal('tanggal_mulai', 'Pengajuan hanya bisa untuk maksimal ' . self::MAKS_HARI_LALU . ' hari ke belakang.');
        }
        if ($selesai->gt($hariIni->copy()->addDays(self::MAKS_HARI_DEPAN))) {
            return $this->gagal('tanggal_selesai', 'Pengajuan hanya bisa untuk maksimal ' . self::MAKS_HARI_DEPAN . ' hari ke depan.');
        }
        if ($selesai->diffInDays($mulai, true) >= self::MAKS_RENTANG) {
            return $this->gagal('tanggal_selesai', 'Satu pengajuan maksimal ' . self::MAKS_RENTANG . ' hari.');
        }

        $hari = PengajuanIzin::hariSekolah($mulai, $selesai);
        if (empty($hari)) {
            return $this->gagal('tanggal_mulai', 'Tanggal yang dipilih jatuh pada Sabtu/Minggu (bukan hari sekolah).');
        }

        // Tidak boleh tumpang tindih dengan pengajuan aktif
        $bentrok = PengajuanIzin::where('siswa_id', $anak->id)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->whereDate('tanggal_mulai', '<=', $selesai->toDateString())
            ->whereDate('tanggal_selesai', '>=', $mulai->toDateString())
            ->exists();
        if ($bentrok) {
            return $this->gagal('tanggal_mulai', 'Sudah ada pengajuan (menunggu/disetujui) pada tanggal tersebut.');
        }

        // Tidak boleh jika anak sudah absen masuk pada tanggal itu
        $sudahHadir = Absensi::where('siswa_id', $anak->id)
            ->whereIn('tanggal', array_map(fn ($d) => $d->toDateString(), $hari))
            ->whereNotNull('jam_masuk')->pluck('tanggal');
        if ($sudahHadir->isNotEmpty()) {
            $daftar = $sudahHadir->map(fn ($t) => Carbon::parse($t)->format('d/m/Y'))->implode(', ');
            return $this->gagal('tanggal_mulai', 'Anak sudah tercatat absen masuk pada: ' . $daftar . '.');
        }

        // Berkas disimpan di disk PRIVATE (tidak bisa diakses lewat URL publik)
        $foto  = $request->file('foto_bukti')->store('pengajuan/' . $anak->id, 'local');
        $surat = $request->hasFile('surat_dokter')
                    ? $request->file('surat_dokter')->store('pengajuan/' . $anak->id, 'local')
                    : null;

        PengajuanIzin::create([
            'siswa_id'        => $anak->id,
            'ortu_id'         => auth()->id(),
            'tanggal_mulai'   => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'jenis'           => $request->jenis,
            'alasan'          => $request->alasan,
            'foto_bukti'      => $foto,
            'surat_dokter'    => $surat,
            'status'          => 'menunggu',
        ]);

        session(['ortu_anak_id' => $anak->id]);

        return redirect()->route('orang_tua.dashboard')
            ->with('success', 'Pengajuan ' . $request->jenis . ' untuk ' . $anak->nama . ' berhasil dikirim dan menunggu verifikasi guru.');
    }

    /** Batalkan pengajuan (hanya yang masih menunggu) */
    public function izinBatal(PengajuanIzin $pengajuan)
    {
        abort_unless($pengajuan->ortu_id === auth()->id(), 403);

        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan yang sudah diverifikasi tidak dapat dibatalkan.');
        }

        foreach ([$pengajuan->foto_bukti, $pengajuan->surat_dokter] as $berkas) {
            if ($berkas) Storage::disk('local')->delete($berkas);
        }
        $pengajuan->delete();

        return back()->with('success', 'Pengajuan dibatalkan.');
    }

    // =====================================================
    //  HELPER
    // =====================================================

    /** Anak yang sedang dipilih (disimpan di session; selalu dicek milik akun ini) */
    private function anakTerpilih(Request $request): array
    {
        $anakList = auth()->user()->anak()->orderBy('nama')->get();
        if ($anakList->isEmpty()) {
            return [$anakList, null];
        }

        if ($request->filled('anak') && $anakList->contains('id', (int) $request->anak)) {
            session(['ortu_anak_id' => (int) $request->anak]);
        }

        $anak = $anakList->firstWhere('id', session('ortu_anak_id')) ?? $anakList->first();
        session(['ortu_anak_id' => $anak->id]);

        return [$anakList, $anak];
    }

    private function statusHariIni($anak): array
    {
        $today = Carbon::today();
        $absen = Absensi::where('siswa_id', $anak->id)
                    ->whereDate('tanggal', $today->toDateString())->first();

        if ($absen) {
            if ($absen->status === 'hadir') {
                $masuk     = $absen->jam_masuk  ? substr($absen->jam_masuk, 0, 5)  : '-';
                $pulang    = $absen->jam_pulang ? substr($absen->jam_pulang, 0, 5) : 'belum absen pulang';
                $terlambat = $absen->jam_masuk && $absen->jam_masuk > self::BATAS_TERLAMBAT;
                return [
                    'key'    => $terlambat ? 'terlambat' : 'hadir',
                    'judul'  => $terlambat ? 'Hadir (Terlambat)' : 'Hadir',
                    'detail' => 'Masuk ' . $masuk . ' • Pulang ' . $pulang,
                ];
            }
            $label = ['izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha (tanpa keterangan)'][$absen->status]
                     ?? ucfirst($absen->status);
            return ['key' => $absen->status, 'judul' => $label, 'detail' => $absen->keterangan ?: 'Dicatat oleh sekolah'];
        }

        if ($today->isWeekend()) {
            return ['key' => 'libur', 'judul' => 'Hari libur', 'detail' => 'Sabtu/Minggu tidak ada kegiatan belajar'];
        }

        $pending = PengajuanIzin::where('siswa_id', $anak->id)->where('status', 'menunggu')
            ->whereDate('tanggal_mulai', '<=', $today->toDateString())
            ->whereDate('tanggal_selesai', '>=', $today->toDateString())->first();
        if ($pending) {
            return ['key' => 'menunggu', 'judul' => 'Menunggu verifikasi', 'detail' => 'Pengajuan ' . $pending->jenis . ' sedang diperiksa guru'];
        }

        return ['key' => 'belum', 'judul' => 'Belum absen', 'detail' => 'Belum ada catatan kehadiran hari ini'];
    }

    private function bulanValid(?string $v): ?Carbon
    {
        if (!$v || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $v)) return null;
        return Carbon::createFromFormat('Y-m-d', $v . '-01')->startOfDay();
    }

    private function tanggalValid(?string $v): ?string
    {
        if (!$v || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
        try {
            return Carbon::createFromFormat('Y-m-d', $v)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function gagal(string $field, string $pesan)
    {
        return back()->withErrors([$field => $pesan])->withInput();
    }
}