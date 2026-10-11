<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiswaController extends Controller
{
    public function dashboard(): View
    {
        $siswa = auth()->user()->siswa;
        $today = Carbon::today()->toDateString();

        $absensiHariIni = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', $today)
            ->first();

        $riwayat = Absensi::where('siswa_id', $siswa->id)
            ->orderBy('tanggal', 'desc')
            ->limit(8)
            ->get();

        $statistik = $this->getStatistikSiswa($siswa->id);

        return view('siswa.dashboard', compact('siswa', 'absensiHariIni', 'riwayat', 'statistik'));
    }

    public function absenMasuk(Request $request): JsonResponse
    {
        $request->validate([
            'foto' => 'required|string',
        ]);

        $siswa = auth()->user()->siswa;
        $today = Carbon::today()->toDateString();

        $cek = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', $today)
            ->first();

        if ($cek && in_array($cek->status, ['izin', 'sakit']) && !$cek->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Hari ini tercatat ' . $cek->status . ' (disetujui guru), absen masuk tidak diperlukan.',
            ]);
        }

        $fotoPath = $this->saveBase64($request->foto, 'absensi/masuk');

        Absensi::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tanggal'  => $today,
            ],
            [
                'jam_masuk'  => now()->toTimeString(),
                'foto_masuk' => $fotoPath,
                'status'     => 'hadir',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil',
        ]);
    }

    public function absenPulang(Request $request): JsonResponse
    {
        $request->validate([
            'foto' => 'required|string',
        ]);

        $siswa = auth()->user()->siswa;
        $today = Carbon::today()->toDateString();

        $absensi = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', $today)
            ->first();

        if (!$absensi || !$absensi->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Belum absen masuk',
            ]);
        }

        if ($absensi->jam_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah absen pulang',
            ]);
        }

        $fotoPath = $this->saveBase64($request->foto, 'absensi/pulang');

        $absensi->update([
            'jam_pulang'  => now()->toTimeString(),
            'foto_pulang' => $fotoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil',
        ]);
    }

    public function riwayat(Request $request): View
    {
        $data = $this->dataRiwayat($request);

        return view('siswa.riwayat', $data);
    }

    public function riwayatLive(Request $request): JsonResponse
    {
        $data = $this->dataRiwayat($request);

        $stats = view('siswa._riwayat_stats', $data)->render();
        $tabel = view('siswa._riwayat_tabel', $data)->render();

        return response()->json([
            'hash'  => md5($stats . $tabel),
            'stats' => $stats,
            'tabel' => $tabel,
            'time'  => now()->format('H:i:s'),
        ]);
    }

    private function dataRiwayat(Request $request): array
    {
        $siswa = auth()->user()->siswa;
        $query = Absensi::where('siswa_id', $siswa->id);

        // Filter Status
        if ($request->filled('status')) {
            if ($request->status === 'terlambat') {
                $query->where('status', 'hadir')
                    ->whereNotNull('jam_masuk')
                    ->where('jam_masuk', '>', '07:00:00');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter Minggu
        if ($request->filled('minggu')) {
            $minggu = (int) $request->minggu;
            $start = now()->startOfMonth()->addWeeks($minggu - 1)->startOfWeek();
            $end = (clone $start)->endOfWeek();

            $query->whereBetween('tanggal', [
                $start->toDateString(),
                $end->toDateString(),
            ]);
        }

        // Data Riwayat
        $riwayat = $query->orderBy('tanggal', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Statistik
        $statistik = $this->getStatistikSiswa($siswa->id);
        $totalHari = array_sum($statistik);
        $persentase = $totalHari > 0 ? round(($statistik['hadir'] / $totalHari) * 100, 1) : 0;

        return compact('riwayat', 'statistik', 'siswa', 'persentase', 'totalHari');
    }

    /**
     * Mengambil statistik kehadiran siswa dalam 1 query DB.
     */
    private function getStatistikSiswa(int $siswaId): array
    {
        $stats = Absensi::where('siswa_id', $siswaId)
            ->selectRaw("
                COUNT(CASE WHEN status = 'hadir' AND jam_masuk <= '07:00:00' THEN 1 END) as hadir,
                COUNT(CASE WHEN status = 'hadir' AND jam_masuk > '07:00:00' THEN 1 END) as terlambat,
                COUNT(CASE WHEN status = 'izin' THEN 1 END) as izin,
                COUNT(CASE WHEN status = 'sakit' THEN 1 END) as sakit,
                COUNT(CASE WHEN status = 'alpha' THEN 1 END) as alpha
            ")
            ->first();

        return [
            'hadir'     => (int) ($stats->hadir ?? 0),
            'terlambat' => (int) ($stats->terlambat ?? 0),
            'izin'      => (int) ($stats->izin ?? 0),
            'sakit'     => (int) ($stats->sakit ?? 0),
            'alpha'     => (int) ($stats->alpha ?? 0),
        ];
    }

    /**
     * Helper untuk menyimpan gambar dari format Base64.
     */
   private function saveBase64(string $base64String, string $folder): ?string
    {
        if (empty($base64String)) {
            return null;
        }

        // Kembalikan string base64 utuh untuk disimpan di DB
        return $base64String;
    }
}   