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
        
        if (!$path) {
            abort(404, 'Berkas tidak ditemukan');
        }

        // 1. JIKA FILE BERFORMAT BASE64 (Untuk Vercel / Supabase)
        if (str_starts_with($path, 'data:')) {
            @list($type, $dataClean) = explode(';', $path);
            @list(, $dataClean)      = explode(',', $dataClean);
            @list(, $mime)           = explode(':', $type);

            return response(base64_decode($dataClean), 200, [
                'Content-Type'        => $mime,
                'Content-Disposition' => 'inline',
            ]);
        }

        // 2. FALLBACK JIKA MASIH FILE LOKAL (Development/Localhouse)
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        abort(404, 'Berkas fisik sudah tidak tersedia');
    }
}