<?php

namespace App\Services;

use App\Models\GuruMapel;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PenugasanGuru
{
    const MIN_MAPEL = 1;
    const MAKS_MAPEL = 3;

    /**
     * Simpan (ganti seluruh) penugasan seorang guru.
     * Dipanggil dari controller admin dan seeder, jadi aturan bisnis tidak bergantung pada form.
     *
     * @param array $items [['mapel_id' => 1, 'kelas' => ['XII IPA 1', 'XII IPA 2']], ...]
     */
    public static function simpan(User $guru, array $items): void
    {
        if ($guru->role !== 'guru') {
            self::gagal('Penugasan mata pelajaran hanya untuk akun dengan role Guru.');
        }

        // ---- normalisasi ----
        $peta = [];
        foreach ($items as $item) {
            $mapelId = (int) ($item['mapel_id'] ?? 0);
            if ($mapelId <= 0) continue;

            if (isset($peta[$mapelId])) {
                self::gagal('Mata pelajaran yang sama dipilih lebih dari satu kali.');
            }

            $peta[$mapelId] = collect($item['kelas'] ?? [])
                ->map(fn ($k) => trim((string) $k))->filter()->unique()->values()->all();
        }

        // ---- 1 sampai 3 mata pelajaran ----
        $jumlah = count($peta);
        if ($jumlah < self::MIN_MAPEL) {
            self::gagal('Guru harus memiliki minimal ' . self::MIN_MAPEL . ' mata pelajaran.');
        }
        if ($jumlah > self::MAKS_MAPEL) {
            self::gagal('Guru tidak boleh memiliki lebih dari ' . self::MAKS_MAPEL . ' mata pelajaran.');
        }

        // ---- mapel valid ----
        $mapels = Mapel::whereIn('id', array_keys($peta))->get()->keyBy('id');
        if ($mapels->count() !== $jumlah) {
            self::gagal('Terdapat mata pelajaran yang tidak valid.');
        }

        // ---- kelas valid & minimal 1 kelas per mapel ----
        $kelasValid = Siswa::distinct()->pluck('kelas')->all();
        foreach ($peta as $mapelId => $kelasList) {
            if (empty($kelasList)) {
                self::gagal('Pilih minimal 1 kelas untuk mata pelajaran ' . $mapels[$mapelId]->nama . '.');
            }
            foreach ($kelasList as $kelas) {
                if (!in_array($kelas, $kelasValid, true)) {
                    self::gagal('Kelas "' . $kelas . '" tidak terdaftar.');
                }
            }
        }

        // ---- satu mapel + kelas hanya boleh diampu satu guru ----
        foreach ($peta as $mapelId => $kelasList) {
            $bentrok = GuruMapel::with('guru')
                ->where('mapel_id', $mapelId)
                ->whereIn('kelas', $kelasList)
                ->where('guru_id', '!=', $guru->id)
                ->first();

            if ($bentrok) {
                self::gagal($mapels[$mapelId]->nama . ' kelas ' . $bentrok->kelas
                    . ' sudah diampu oleh ' . ($bentrok->guru->name ?? 'guru lain') . '.');
            }
        }

        // ---- simpan (ganti seluruh penugasan guru ini) ----
        DB::transaction(function () use ($guru, $peta) {
            GuruMapel::where('guru_id', $guru->id)->delete();

            foreach ($peta as $mapelId => $kelasList) {
                foreach ($kelasList as $kelas) {
                    GuruMapel::create([
                        'guru_id'  => $guru->id,
                        'mapel_id' => $mapelId,
                        'kelas'    => $kelas,
                    ]);
                }
            }
        });
    }

    private static function gagal(string $pesan): void
    {
        throw ValidationException::withMessages(['penugasan' => $pesan]);
    }
}