<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $kelasDiampu = auth()->user()->kelasDiampu();

        $status = $request->get('status', 'menunggu');
        $kelas  = $kelasDiampu->contains($request->get('kelas')) ? $request->get('kelas') : null;
        $q      = $request->get('q');

        $pengajuans = PengajuanIzin::with(['siswa', 'ortu', 'verifikator'])
            ->whereHas('siswa', fn ($s) => $s->whereIn('kelas', $kelasDiampu))
            ->when(in_array($status, ['menunggu', 'disetujui', 'ditolak']),
                fn ($x) => $x->where('status', $status))
            ->when($kelas, fn ($x) => $x->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
            ->when($q, fn ($x) => $x->whereHas('siswa', fn ($s) => $s->where(
                fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%")
            )))
            ->orderByRaw("CASE status WHEN 'menunggu' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $jumlah = PengajuanIzin::whereHas('siswa', fn ($s) => $s->whereIn('kelas', $kelasDiampu))
            ->selectRaw('status, COUNT(*) as jml')->groupBy('status')->pluck('jml', 'status');
        $kelasList = $kelasDiampu;

        return view('guru.pengajuan.index', compact('pengajuans', 'jumlah', 'kelasList', 'status', 'kelas', 'q'));
    }

    public function setujui(Request $request, PengajuanIzin $pengajuan)
    {
        $this->otorisasi($pengajuan);
        $request->validate(['catatan' => 'nullable|string|max:500']);

        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diverifikasi sebelumnya.');
        }

        $dicatat = 0;
        $dilewati = 0;

        DB::transaction(function () use ($pengajuan, $request, &$dicatat, &$dilewati) {
            $hari = PengajuanIzin::hariSekolah($pengajuan->tanggal_mulai, $pengajuan->tanggal_selesai);

            foreach ($hari as $tgl) {
                $absen = Absensi::where('siswa_id', $pengajuan->siswa_id)
                            ->whereDate('tanggal', $tgl->toDateString())->first();

                // Siswa sudah absen masuk sendiri pada hari itu → jangan ditimpa
                if ($absen && $absen->jam_masuk) {
                    $dilewati++;
                    continue;
                }

                $data = [
                    'status'       => $pengajuan->jenis,
                    'keterangan'   => ucfirst($pengajuan->jenis) . ' (diajukan orang tua): ' . $pengajuan->alasan,
                    'pengajuan_id' => $pengajuan->id,
                ];

                if ($absen) {
                    $absen->update($data);
                } else {
                    Absensi::create($data + [
                        'siswa_id' => $pengajuan->siswa_id,
                        'tanggal'  => $tgl->toDateString(),
                    ]);
                }
                $dicatat++;
            }

            $pengajuan->update([
                'status'             => 'disetujui',
                'catatan_verifikasi' => $request->catatan,
                'diverifikasi_oleh'  => auth()->id(),
                'diverifikasi_at'    => now(),
            ]);
        });

        $pesan = "Pengajuan {$pengajuan->siswa->nama} disetujui ({$dicatat} hari dicatat sebagai {$pengajuan->jenis}";
        $pesan .= $dilewati ? ", {$dilewati} hari dilewati karena siswa sudah absen)." : ').';

        return back()->with('success', $pesan);
    }

    public function tolak(Request $request, PengajuanIzin $pengajuan)
    {
        $this->otorisasi($pengajuan);
        $request->validate(
            ['catatan' => 'required|string|min:5|max:500'],
            ['catatan.required' => 'Alasan penolakan wajib diisi pada kolom catatan pemeriksaan.',
             'catatan.min'      => 'Alasan penolakan minimal 5 karakter agar jelas bagi orang tua.']
        );

        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diverifikasi sebelumnya.');
        }

        $pengajuan->update([
            'status'             => 'ditolak',
            'catatan_verifikasi' => $request->catatan,
            'diverifikasi_oleh'  => auth()->id(),
            'diverifikasi_at'    => now(),
        ]);

        return back()->with('success', "Pengajuan {$pengajuan->siswa->nama} ditolak.");
    }

    /** Guru hanya boleh memverifikasi pengajuan siswa di kelas yang ia ajar */
    private function otorisasi(PengajuanIzin $pengajuan): void
    {
        abort_unless(auth()->user()->bolehKelas($pengajuan->siswa->kelas ?? null), 403);
    }
}