<?php

namespace Database\Seeders;

use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class NilaiSeeder extends Seeder
{
    public function run(): void
    {
        $mapels = [
            ['nama' => 'Pendidikan Agama Islam', 'kode' => 'PAI'],
            ['nama' => 'Pendidikan Pancasila',   'kode' => 'PPKn'],
            ['nama' => 'Bahasa Indonesia',       'kode' => 'BIN'],
            ['nama' => 'Matematika',             'kode' => 'MTK'],
            ['nama' => 'Bahasa Inggris',         'kode' => 'BIG'],
            ['nama' => 'Fisika',                 'kode' => 'FIS'],
            ['nama' => 'Kimia',                  'kode' => 'KIM'],
            ['nama' => 'Biologi',                'kode' => 'BIO'],
            ['nama' => 'Sejarah',                'kode' => 'SEJ'],
            ['nama' => 'Ekonomi',                'kode' => 'EKO'],
        ];

        foreach ($mapels as $m) {
            Mapel::firstOrCreate(['kode' => $m['kode']], $m);
        }

        $semester = '2024/2025 Ganjil';

        foreach (Siswa::all() as $siswa) {
            foreach (Mapel::all() as $mapel) {
                $tugas = rand(70, 100);
                $uts   = rand(65, 95);
                $uas   = rand(70, 98);
                Nilai::updateOrCreate(
                    [
                        'siswa_id' => $siswa->id,
                        'mapel_id' => $mapel->id,
                        'semester' => $semester,
                    ],
                    [
                        'tugas' => $tugas,
                        'uts' => $uts,
                        'uas' => $uas,
                        'nilai_akhir' => Nilai::hitungAkhir($tugas, $uts, $uas),
                    ]
                );
            }
        }
    }
}