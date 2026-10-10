<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzin;
use Illuminate\Support\Facades\Storage;

class BerkasController extends Controller
{
    /**
     * Foto bukti / surat dokter pengajuan.
     * Boleh dibuka oleh: admin, guru yang mengajar kelas siswa tersebut,
     * dan orang tua pengaju sendiri.
     */
    public function pengajuan(PengajuanIzin $pengajuan, string $jenis)
    {
        $user = auth()->user();

        $boleh = $user->role === 'admin'
              || ($user->role === 'guru' && $user->bolehKelas($pengajuan->siswa->kelas ?? null))
              || ($user->role === 'orang_tua' && $pengajuan->ortu_id === $user->id);
        abort_unless($boleh, 403);

        $path = $jenis === 'foto' ? $pengajuan->foto_bukti : $pengajuan->surat_dokter;
        abort_if(!$path || !Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}