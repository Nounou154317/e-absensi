<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function dashboard()
    {
        $guru      = auth()->user();
        $penugasan = $guru->ringkasanPenugasan();
        $kelas     = $penugasan['kelas'];

        // Hanya siswa di kelas yang diajar guru ini
        $totalSiswa   = $penugasan['jumlah_siswa'];
        $hadirHariIni = Absensi::whereDate('tanggal', today())
                                ->where('status', 'hadir')
                                ->whereHas('siswa', fn ($q) => $q->whereIn('kelas', $kelas))
                                ->count();
        $absensiHariIni = Absensi::with('siswa')
                                ->whereDate('tanggal', today())
                                ->whereHas('siswa', fn ($q) => $q->whereIn('kelas', $kelas))
                                ->latest()->get();

        $penugasanHash = $guru->hashPenugasan();

        return view('guru.dashboard', compact(
            'totalSiswa', 'hadirHariIni', 'absensiHariIni', 'penugasan', 'penugasanHash'
        ));
    }

    /** Dipanggil berkala oleh dashboard: bila hash berubah (admin mengubah penugasan), halaman dimuat ulang */
    public function penugasanLive()
    {
        return response()->json(['hash' => auth()->user()->hashPenugasan()]);
    }

    public function rekap(Request $request)
    {
        $kelasDiampu = auth()->user()->kelasDiampu();

        $query = Absensi::with('siswa')
            ->whereHas('siswa', fn ($q) => $q->whereIn('kelas', $kelasDiampu));

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        // Filter kelas hanya berlaku untuk kelas yang memang diajar
        if ($request->filled('kelas') && $kelasDiampu->contains($request->kelas)) {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas', $request->kelas));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $absensis  = $query->latest()->paginate(20)->withQueryString();
        $kelasList = $kelasDiampu;

        return view('guru.rekap', compact('absensis', 'kelasList'));
    }

    public function updateAbsensi(Request $request, Absensi $absensi)
    {
        // Hanya boleh mengubah absensi siswa di kelas yang diajar
        abort_unless(auth()->user()->bolehKelas($absensi->siswa->kelas ?? null), 403);

        $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable',
        ]);

        $absensi->update($request->only('status', 'keterangan'));
        return back()->with('success', 'Absensi berhasil diupdate');
    }
}