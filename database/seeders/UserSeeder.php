<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ==== ADMIN ====
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // ==== GURU ====
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'guru@sekolah.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'mata_pelajaran' => 'Matematika',
        ]);

        // ==== ORANG TUA ====
        $ortu = User::create([
            'name' => 'Bambang',
            'email' => 'ortu@sekolah.com',
            'password' => Hash::make('password'),
            'role' => 'orang_tua',
        ]);

        // ==== SISWA 1 ====
        $u1 = User::create([
            'name' => 'Ani Lestari',
            'email' => 'siswa@sekolah.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nis' => '2024001',
            'kelas' => 'XII IPA 1',
        ]);

        $s1 = Siswa::create([
            'user_id' => $u1->id,
            'ortu_id' => $ortu->id,
            'nis' => '2024001',
            'nama' => 'Ani Lestari',
            'kelas' => 'XII IPA 1',
            'jenis_kelamin' => 'P',
        ]);

        // ==== SISWA 2 ====
        $u2 = User::create([
            'name' => 'Dodi Pratama',
            'email' => 'dodi@sekolah.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nis' => '2024002',
            'kelas' => 'XII IPA 1',
        ]);

        $s2 = Siswa::create([
            'user_id' => $u2->id,
            'ortu_id' => $ortu->id,
            'nis' => '2024002',
            'nama' => 'Dodi Pratama',
            'kelas' => 'XII IPA 1',
            'jenis_kelamin' => 'L',
        ]);
    }
}