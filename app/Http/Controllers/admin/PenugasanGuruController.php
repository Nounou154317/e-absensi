<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Services\PenugasanGuru;
use Illuminate\Http\Request;

class PenugasanGuruController extends Controller
{
    public function index()
    {
        $gurus = User::where('role', 'guru')->orderBy('name')->get();

        return view('admin.penugasan.index', compact('gurus'));
    }

    public function edit(User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        $mapelList = Mapel::orderBy('nama')->get();
        $kelasList = Siswa::distinct()->orderBy('kelas')->pluck('kelas');

        // Penugasan saat ini (atau input lama bila validasi gagal)
        $saatIni = $guru->ringkasanPenugasan()['per_mapel']
            ->map(fn ($x) => ['mapel_id' => $x['mapel']->id, 'kelas' => $x['kelas']])
            ->all();

        $blok = collect(old('penugasan', $saatIni))
            ->map(fn ($b) => [
                'mapel_id' => (int) ($b['mapel_id'] ?? 0) ?: null,
                'kelas'    => array_values($b['kelas'] ?? []),
            ])->values()->all();

        if (empty($blok)) {
            $blok = [['mapel_id' => null, 'kelas' => []]];
        }

        // Kombinasi mapel+kelas yang sudah diampu guru LAIN (untuk dinonaktifkan di form)
        $terpakai = GuruMapel::with('guru')
            ->where('guru_id', '!=', $guru->id)->get()
            ->groupBy('mapel_id')
            ->map(fn ($g) => $g->mapWithKeys(fn ($r) => [$r->kelas => $r->guru->name ?? 'guru lain']))
            ->toArray();

        return view('admin.penugasan.edit', compact('guru', 'mapelList', 'kelasList', 'blok', 'terpakai'));
    }

    public function update(Request $request, User $guru)
    {
        abort_unless($guru->role === 'guru', 404);

        // Validasi bentuk data (backend, bukan hanya tampilan)
        $data = $request->validate([
            'penugasan'                 => ['required', 'array', 'min:1', 'max:' . PenugasanGuru::MAKS_MAPEL],
            'penugasan.*.mapel_id'      => ['required', 'integer', 'distinct', 'exists:mapels,id'],
            'penugasan.*.kelas'         => ['required', 'array', 'min:1'],
            'penugasan.*.kelas.*'       => ['required', 'string', 'max:50'],
        ], [
            'penugasan.required'            => 'Guru harus memiliki minimal 1 mata pelajaran.',
            'penugasan.min'                 => 'Guru harus memiliki minimal 1 mata pelajaran.',
            'penugasan.max'                 => 'Guru tidak boleh memiliki lebih dari ' . PenugasanGuru::MAKS_MAPEL . ' mata pelajaran.',
            'penugasan.*.mapel_id.required' => 'Pilih mata pelajaran pada setiap blok penugasan.',
            'penugasan.*.mapel_id.distinct' => 'Mata pelajaran yang sama dipilih lebih dari satu kali.',
            'penugasan.*.mapel_id.exists'   => 'Mata pelajaran tidak valid.',
            'penugasan.*.kelas.required'    => 'Pilih minimal 1 kelas untuk setiap mata pelajaran.',
            'penugasan.*.kelas.min'         => 'Pilih minimal 1 kelas untuk setiap mata pelajaran.',
        ]);

        // Aturan bisnis (1–3 mapel, kelas valid, tidak bentrok dengan guru lain)
        PenugasanGuru::simpan($guru, $data['penugasan']);

        return redirect()->route('admin.penugasan.index')
            ->with('success', 'Penugasan ' . $guru->name . ' berhasil disimpan.');
    }
}