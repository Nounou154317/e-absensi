<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Absensi extends Model
{
    use HasFactory;

    /** Bobot nilai absensi terhadap nilai akhir (10%) */
    const BOBOT = 0.10;

    /** Jumlah hari efektif dalam 1 tahun ajaran */
    const HARI_EFEKTIF = 185;

    protected $fillable = [
        'siswa_id', 'tanggal', 'jam_masuk', 'jam_pulang',
        'foto_masuk', 'foto_pulang', 'status', 'keterangan', 'pengajuan_id',
    ];

    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function pengajuan() { return $this->belongsTo(PengajuanIzin::class, 'pengajuan_id'); }

    // =====================================================
    //  PENILAIAN ABSENSI
    //  Persentase = (Jumlah Hari Hadir / 185) × 100%
    // =====================================================

    /**
     * 95–100 → Sangat Baik | 90–94 → Baik | 85–89 → Cukup
     * 75–84  → Kurang      | <75   → Buruk
     */
    public static function kategoriKehadiran(float $persen): string
    {
        if ($persen >= 95) return 'Sangat Baik';
        if ($persen >= 90) return 'Baik';
        if ($persen >= 85) return 'Cukup';
        if ($persen >= 75) return 'Kurang';
        return 'Buruk';
    }

    /** 95–100 → 100 | 90–94 → 90 | 85–89 → 80 | 75–84 → 70 | <75 → 60 */
    public static function nilaiAbsensi(float $persen): int
    {
        if ($persen >= 95) return 100;
        if ($persen >= 90) return 90;
        if ($persen >= 85) return 80;
        if ($persen >= 75) return 70;
        return 60;
    }

    /** Definisi tiap kategori kehadiran */
    public static function definisiKategori(?string $kategori): ?string
    {
        return match ($kategori) {
            'Sangat Baik' => 'Tingkat kehadiran sangat tinggi dan hampir selalu mengikuti kegiatan pembelajaran.',
            'Baik'        => 'Tingkat kehadiran baik dengan sedikit ketidakhadiran.',
            'Cukup'       => 'Tingkat kehadiran masih cukup, tetapi mulai terdapat ketidakhadiran yang perlu diperhatikan.',
            'Kurang'      => 'Cukup sering tidak hadir sehingga dapat mulai memengaruhi proses pembelajaran dan nilai.',
            'Buruk'       => 'Tingkat ketidakhadiran tinggi sehingga berpotensi serius memengaruhi proses pembelajaran dan hasil akademik.',
            default       => null,
        };
    }

    /** Warna badge per kategori [background, teks] */
    public static function warnaKategori(?string $kategori): array
    {
        return match ($kategori) {
            'Sangat Baik' => ['#d1fae5', '#065f46'],
            'Baik'        => ['#dbeafe', '#1e40af'],
            'Cukup'       => ['#fef3c7', '#92400e'],
            'Kurang'      => ['#ffedd5', '#9a3412'],
            'Buruk'       => ['#fee2e2', '#991b1b'],
            default       => ['#f1f5f9', '#64748b'],
        };
    }

    /** Semester berjalan, mis. "2026/2027 Ganjil" (Ganjil: Jul–Des, Genap: Jan–Jun) */
    public static function semesterSekarang(): string
    {
        $now = Carbon::now();
        if ($now->month >= 7) {
            return $now->year . '/' . ($now->year + 1) . ' Ganjil';
        }
        return ($now->year - 1) . '/' . $now->year . ' Genap';
    }

    /** Rentang 1 TAHUN AJARAN [1 Juli – 30 Juni] dari string semester; null jika format tidak dikenali */
    public static function rentangTahunAjaran(string $semester): ?array
    {
        if (!preg_match('#^(\d{4})/(\d{4}) (Ganjil|Genap)$#', trim($semester), $m)) {
            return null;
        }
        return [Carbon::create((int) $m[1], 7, 1)->toDateString(),
                Carbon::create((int) $m[2], 6, 30)->toDateString()];
    }

    /**
     * Penilaian absensi seorang siswa untuk 1 tahun ajaran.
     * Persentase kehadiran = (jumlah hari hadir ÷ 185) × 100.
     * Hari "hadir" termasuk yang terlambat; izin, sakit, dan alpha tidak dihitung hadir.
     */
    public static function penilaian(int $siswaId, ?string $semester = null): array
    {
        $q = static::where('siswa_id', $siswaId);

        if ($semester && ($rentang = static::rentangTahunAjaran($semester))) {
            $q->whereBetween('tanggal', $rentang);
        }

        $per = (clone $q)->selectRaw('status, COUNT(*) as jml')
                         ->groupBy('status')->pluck('jml', 'status');

        $hadir = (int) ($per['hadir'] ?? 0);
        $izin  = (int) ($per['izin']  ?? 0);
        $sakit = (int) ($per['sakit'] ?? 0);
        $alpha = (int) ($per['alpha'] ?? 0);
        $tercatat = $hadir + $izin + $sakit + $alpha;

        // Belum ada satu pun data absensi → belum bisa dinilai
        $persen   = $tercatat > 0
                    ? min(100, round(($hadir / static::HARI_EFEKTIF) * 100, 2))
                    : null;
        $kategori = $persen !== null ? static::kategoriKehadiran($persen) : null;
        $nilai    = $persen !== null ? static::nilaiAbsensi($persen) : null;

        return [
            'hadir' => $hadir, 'izin' => $izin, 'sakit' => $sakit, 'alpha' => $alpha,
            'total'         => static::HARI_EFEKTIF,   // pembagi = 185 hari efektif
            'hari_efektif'  => static::HARI_EFEKTIF,
            'persentase'    => $persen,
            'kategori'      => $kategori,
            'definisi'      => static::definisiKategori($kategori),
            'nilai_absensi' => $nilai,
            'bobot'         => static::BOBOT,
            'kontribusi'    => $nilai !== null ? round($nilai * static::BOBOT, 2) : null,
            'warna'         => static::warnaKategori($kategori),
        ];
    }
}