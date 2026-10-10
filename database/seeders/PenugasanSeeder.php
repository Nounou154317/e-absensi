<?php

namespace Database\Seeders;

use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Services\PenugasanGuru;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PenugasanSeeder extends Seeder
{
    public function run(): void
    {
        $kelas = Siswa::distinct()->pluck('kelas')->all();
        $id = fn (string $kode) => Mapel::where('kode', $kode)->value('id');

        // Guru 1: Matematika & Fisika
        $guru1 = User::where('email', 'guru@sekolah.com')->first();
        if ($guru1) {
            PenugasanGuru::simpan($guru1, [
                ['mapel_id' => $id('MTK'), 'kelas' => $kelas],
                ['mapel_id' => $id('FIS'), 'kelas' => $kelas],
            ]);
        }

        // Guru 2: Bahasa Indonesia, Bahasa Inggris, PAI
        $guru2 = User::firstOrCreate(
            ['email' => 'guru2@sekolah.com'],
            ['name' => 'Siti Rahma', 'password' => Hash::make('password'), 'role' => 'guru']
        );
        PenugasanGuru::simpan($guru2, [
            ['mapel_id' => $id('BIN'), 'kelas' => $kelas],
            ['mapel_id' => $id('BIG'), 'kelas' => $kelas],
            ['mapel_id' => $id('PAI'), 'kelas' => $kelas],
        ]);
    }
}