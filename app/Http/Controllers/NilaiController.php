<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    const KKM = 75;

    /**
     * Siswa HANYA menerima hasil akhir (nilai akhir per mapel + nilai akhir keseluruhan
     * + kategori kehadiran). Rincian Tugas/UTS/UAS, nilai absensi, bobot, dan rumus
     * hanya ada di bagian guru.
     * Hasil baru tampil setelah guru menginput SEMUA mata pelajaran.
     */
    public function index(Request $request)
    {
        $siswa    = auth()->user()->siswa;
        $semester = $request->get('semester', '2026/2027 Ganjil');
        $filter   = $request->get('filter', 'semua'); // semua | tuntas | remedial

        $rekap = Nilai::rekapAkhir($siswa->id, $semester);

        // Hanya nilai akhir per mapel yang diteruskan ke siswa
        $semuaNilai = $rekap['siap']
            ? $rekap['rincian']->map(fn ($r) => [
                'mapel'       => $r['mapel'],
                'nilai_akhir' => $r['nilai_akhir'],
              ])->values()
            : collect();

        $nilais = match ($filter) {
            'tuntas'   => $semuaNilai->filter(fn ($r) => $r['nilai_akhir'] >= self::KKM)->values(),
            'remedial' => $semuaNilai->filter(fn ($r) => $r['nilai_akhir'] <  self::KKM)->values(),
            default    => $semuaNilai,
        };

        $tertinggiRow = $semuaNilai->sortByDesc('nilai_akhir')->first();
        $tertinggi    = $tertinggiRow['nilai_akhir'] ?? 0;
        $mapelTertinggi = $tertinggiRow['mapel'] ?? '-';
        $remedial     = $semuaNilai->where('nilai_akhir', '<', self::KKM)->count();

        $nilaiAkhir = $rekap['nilai_akhir'];
        $absensi    = $rekap['absensi'];

        return view('siswa.nilai', compact(
            'siswa', 'semester', 'filter', 'rekap',
            'semuaNilai', 'nilais', 'nilaiAkhir',
            'tertinggi', 'mapelTertinggi', 'remedial', 'absensi'
        ));
    }
}