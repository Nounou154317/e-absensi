<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index()
    {
        $absensis = Absensi::with('siswa')->latest()->paginate(10);
        return view('absensi.index', compact('absensis'));
    }

    // Form absensi (pilih siswa + kamera)
    public function create()
    {
        $siswas = Siswa::orderBy('nama')->get();
        return view('absensi.create', compact('siswas'));
    }

    // Simpan absensi masuk dengan foto
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'foto' => 'required', // base64
        ]);

        $today = Carbon::today()->toDateString();
        $siswaId = $request->siswa_id;

        // Cek apakah sudah absen masuk hari ini
        $cek = Absensi::where('siswa_id', $siswaId)
                      ->where('tanggal', $today)
                      ->first();

        if ($cek && $cek->jam_masuk) {
            return redirect()->back()->with('error', 'Siswa sudah absen masuk hari ini!');
        }

        // Simpan foto base64
        $fotoPath = $this->saveBase64Image($request->foto, 'absensi/masuk');

        if ($cek) {
            // Update jam masuk jika belum ada
            $cek->update([
                'jam_masuk' => Carbon::now()->toTimeString(),
                'foto_masuk' => $fotoPath,
                'status' => 'hadir',
            ]);
        } else {
            Absensi::create([
                'siswa_id' => $siswaId,
                'tanggal' => $today,
                'jam_masuk' => Carbon::now()->toTimeString(),
                'foto_masuk' => $fotoPath,
                'status' => 'hadir',
            ]);
        }

        return redirect()->route('absensi.index')->with('success', 'Absensi masuk berhasil!');
    }

    // Form absen pulang
    public function createPulang()
    {
        $siswas = Siswa::orderBy('nama')->get();
        return view('absensi.pulang', compact('siswas'));
    }

    public function storePulang(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'foto' => 'required',
        ]);

        $today = Carbon::today()->toDateString();
        $absensi = Absensi::where('siswa_id', $request->siswa_id)
                          ->where('tanggal', $today)
                          ->first();

        if (!$absensi || !$absensi->jam_masuk) {
            return redirect()->back()->with('error', 'Siswa belum absen masuk hari ini!');
        }

        if ($absensi->jam_pulang) {
            return redirect()->back()->with('error', 'Siswa sudah absen pulang hari ini!');
        }

        $fotoPath = $this->saveBase64Image($request->foto, 'absensi/pulang');

        $absensi->update([
            'jam_pulang' => Carbon::now()->toTimeString(),
            'foto_pulang' => $fotoPath,
        ]);

        return redirect()->route('absensi.index')->with('success', 'Absensi pulang berhasil!');
    }

    // Helper simpan base64
    private function saveBase64Image($base64String, $folder)
    {
        // Hapus prefix data:image/png;base64,
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);
        $image = str_replace(' ', '+', $image);
        $imageName = $folder . '/' . uniqid() . '.png';

        Storage::disk('public')->put($imageName, base64_decode($image));
        return $imageName;
    }

    public function destroy(Absensi $absensi)
    {
        if ($absensi->foto_masuk) Storage::disk('public')->delete($absensi->foto_masuk);
        if ($absensi->foto_pulang) Storage::disk('public')->delete($absensi->foto_pulang);
        $absensi->delete();
        return back()->with('success', 'Data absensi dihapus');
    }
}