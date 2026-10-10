<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    const KKM = 75;

    /**
     * Halaman utama: hanya mapel & kelas yang ditugaskan ke guru ini
     */
    public function index(Request $request)
    {
        $guru     = auth()->user();
        $semester = $request->get('semester', '2026/2027 Ganjil');

        // Mapel yang diampu; jika parameter tidak sah → mapel pertama
        $mapelList = $guru->mapelDiampu();
        $mapelId   = (int) $request->get('mapel_id');
        if (!$mapelList->contains('id', $mapelId)) {
            $mapelId = $mapelList->first()->id ?? null;
        }

        // Kelas untuk mapel tersebut; jika parameter tidak sah → kelas pertama
        $kelasList = $mapelId ? $guru->kelasDiampu($mapelId) : collect();
        $kelas     = $request->get('kelas');
        if (!$kelasList->contains($kelas)) {
            $kelas = $kelasList->first();
        }

        $siswas = collect();
        if ($kelas && $mapelId) {
            $siswas = Siswa::where('kelas', $kelas)
                ->with(['nilais' => function ($q) use ($mapelId, $semester) {
                    $q->where('mapel_id', $mapelId)
                      ->where('semester', $semester);
                }])
                ->orderBy('nama')
                ->get();
        }

        $mapelTerpilih = $mapelList->firstWhere('id', $mapelId);

        // Penilaian absensi 1 tahun ajaran tiap siswa (pembagi 185 hari efektif)
        $absensiMap = $siswas->mapWithKeys(
            fn ($s) => [$s->id => Absensi::penilaian($s->id, $semester)]
        );

        return view('guru.nilai.index', compact(
            'kelasList', 'mapelList', 'siswas',
            'kelas', 'mapelId', 'mapelTerpilih', 'semester', 'absensiMap'
        ));
    }

    /**
     * Form input nilai untuk 1 siswa
     */
    public function edit(Siswa $siswa, Request $request)
    {
        $semester = $request->get('semester', '2026/2027 Ganjil');
        $mapel    = Mapel::findOrFail($request->get('mapel_id'));

        abort_unless(auth()->user()->bolehMengajar($mapel->id, $siswa->kelas), 403);

        $nilai = Nilai::where('siswa_id', $siswa->id)
                    ->where('mapel_id', $mapel->id)
                    ->where('semester', $semester)
                    ->first();

        $absensi = Absensi::penilaian($siswa->id, $semester);

        return view('guru.nilai.edit', compact('siswa', 'mapel', 'nilai', 'semester', 'absensi'));
    }

    /**
     * Simpan nilai (create atau update)
     */
    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'semester' => 'required',
            'tugas'    => 'required|numeric|min:0|max:100',
            'uts'      => 'required|numeric|min:0|max:100',
            'uas'      => 'required|numeric|min:0|max:100',
        ]);

        abort_unless(auth()->user()->bolehMengajar((int) $request->mapel_id, $siswa->kelas), 403);

        $tugas = (float) $request->tugas;
        $uts   = (float) $request->uts;
        $uas   = (float) $request->uas;

        // Rumus: Tugas 30% + UTS 25% + UAS 35% + Absensi 10%
        $nilaiAbsensi = Absensi::penilaian($siswa->id, $request->semester)['nilai_absensi'] ?? 0;
        $akhir = Nilai::hitungAkhir($tugas, $uts, $uas, $nilaiAbsensi);

        Nilai::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'mapel_id' => $request->mapel_id,
                'semester' => $request->semester,
            ],
            [
                'tugas'       => $tugas,
                'uts'         => $uts,
                'uas'         => $uas,
                'nilai_akhir' => $akhir,
            ]
        );

        return redirect()
            ->route('guru.nilai.index', [
                'kelas'     => $siswa->kelas,
                'mapel_id'  => $request->mapel_id,
                'semester'  => $request->semester,
            ])
            ->with('success', 'Nilai ' . $siswa->nama . ' berhasil disimpan');
    }

    /**
     * Simpan massal (banyak siswa sekaligus)
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'semester' => 'required',
            'nilai'    => 'required|array',
        ]);

        $guru    = auth()->user();
        $mapelId = (int) $request->mapel_id;

        // Otorisasi SEMUA siswa dulu, sebelum ada yang disimpan
        $siswas = Siswa::whereIn('id', array_keys($request->nilai))->get()->keyBy('id');
        foreach (array_keys($request->nilai) as $siswaId) {
            $siswa = $siswas->get((int) $siswaId);
            abort_unless($siswa && $guru->bolehMengajar($mapelId, $siswa->kelas), 403);
        }

        $count = 0;
        foreach ($request->nilai as $siswaId => $n) {
            $tugas = (float) ($n['tugas'] ?? 0);
            $uts   = (float) ($n['uts'] ?? 0);
            $uas   = (float) ($n['uas'] ?? 0);

            // Skip kalau semua nilai 0 (belum diisi)
            if ($tugas == 0 && $uts == 0 && $uas == 0) {
                continue;
            }

            $nilaiAbsensi = Absensi::penilaian((int) $siswaId, $request->semester)['nilai_absensi'] ?? 0;
            $akhir = Nilai::hitungAkhir($tugas, $uts, $uas, $nilaiAbsensi);

            Nilai::updateOrCreate(
                [
                    'siswa_id' => $siswaId,
                    'mapel_id' => $mapelId,
                    'semester' => $request->semester,
                ],
                [
                    'tugas'       => $tugas,
                    'uts'         => $uts,
                    'uas'         => $uas,
                    'nilai_akhir' => $akhir,
                ]
            );
            $count++;
        }

        return redirect()
            ->back()
            ->with('success', "Berhasil menyimpan {$count} nilai siswa");
    }

    /**
     * Rekap nilai akhir per kelas (hanya kelas yang diajar guru ini).
     */
    public function rekapAkhir(Request $request)
    {
        $kelasList = auth()->user()->kelasDiampu();
        $kelas     = $request->get('kelas');
        if (!$kelasList->contains($kelas)) {
            $kelas = $kelasList->first();
        }
        $semester = $request->get('semester', '2026/2027 Ganjil');

        $rekap = $kelas
            ? Siswa::where('kelas', $kelas)->orderBy('nama')->get()
                ->map(fn ($siswa) => [
                    'siswa' => $siswa,
                    'data'  => Nilai::rekapAkhir($siswa->id, $semester),
                ])
            : collect();

        return view('guru.nilai.akhir', compact('kelasList', 'kelas', 'semester', 'rekap'));
    }
}