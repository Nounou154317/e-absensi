<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    const BOBOT_TUGAS = 0.30;
    const BOBOT_UTS   = 0.25;
    const BOBOT_UAS   = 0.35;
    // Absensi 10% → Absensi::BOBOT
    protected $fillable = [
        'siswa_id', 'mapel_id', 'semester',
        'tugas', 'uts', 'uas', 'nilai_akhir',
    ];

    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function mapel() { return $this->belongsTo(Mapel::class); }

    // Hitung nilai akhir: Tugas 30% + UTS 30% + UAS 40%
    public static function hitungAkhir($tugas, $uts, $uas, $nilaiAbsensi = 0): float
    {
        return round(
            ($tugas * self::BOBOT_TUGAS)
            + ($uts * self::BOBOT_UTS)
            + ($uas * self::BOBOT_UAS)
            + ($nilaiAbsensi * Absensi::BOBOT),
            2
        );
    }
    public static function predikat(float $nilai): string
    {
        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B';
        if ($nilai >= 70) return 'C';
        return 'D';
    }
    public static function rekapAkhir(int $siswaId, string $semester): array
    {
        $kelas    = Siswa::whereKey($siswaId)->value('kelas');
        $mapelIds = GuruMapel::where('kelas', $kelas)->pluck('mapel_id')->unique();
        $mapels   = Mapel::whereIn('id', $mapelIds)->orderBy('nama')->get();
        
        $rows   = static::where('siswa_id', $siswaId)
                        ->where('semester', $semester)
                        ->get()->keyBy('mapel_id');

        $terisi  = $mapels->filter(fn ($m) => $rows->has($m->id))->count();
        $lengkap = $mapels->isNotEmpty() && $terisi === $mapels->count();

        $absensi  = Absensi::penilaian($siswaId, $semester);
        $nilaiAbs = $absensi['nilai_absensi'];

        $rincian = [];
        foreach ($mapels as $m) {
            $n = $rows->get($m->id);
            if (!$n) continue;
            $rincian[] = [
                'mapel'       => $m->nama,
                'tugas'       => (float) $n->tugas,
                'uts'         => (float) $n->uts,
                'uas'         => (float) $n->uas,
                'nilai_akhir' => $nilaiAbs !== null
                    ? self::hitungAkhir($n->tugas, $n->uts, $n->uas, $nilaiAbs)
                    : null,
            ];
        }
        $rincian = collect($rincian);

        $ada = $rincian->isNotEmpty();
        $siap = $lengkap && $nilaiAbs !== null;

        $akhirList  = $rincian->pluck('nilai_akhir')->filter(fn ($v) => $v !== null);
        $nilaiAkhir = ($siap && $akhirList->isNotEmpty()) ? round($akhirList->avg(), 2) : null;

        return [
            'total_mapel' => $mapels->count(),
            'terisi'      => $terisi,
            'lengkap'     => $lengkap,
            'siap'        => $siap,
            'rincian'     => $rincian,
            'rata_tugas'  => $ada ? round($rincian->avg('tugas'), 2) : null,
            'rata_uts'    => $ada ? round($rincian->avg('uts'), 2)   : null,
            'rata_uas'    => $ada ? round($rincian->avg('uas'), 2)   : null,
            'absensi'     => $absensi,
            'nilai_akhir' => $nilaiAkhir,
        ];
    }
}