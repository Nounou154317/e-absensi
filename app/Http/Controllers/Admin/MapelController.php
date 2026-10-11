<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $mapels = Mapel::with('penugasan.guru')
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('nama', 'like', '%' . $request->q . '%')
                      ->orWhere('kode', 'like', '%' . $request->q . '%');
                });
            })
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.mapel.index', compact('mapels'));
    }

    public function create()
    {
        return view('admin.mapel.form', ['mapel' => new Mapel()]);
    }

    public function store(Request $request)
    {
        Mapel::create($this->validasi($request));

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan');
    }

    public function edit(Mapel $mapel)
    {
        return view('admin.mapel.form', compact('mapel'));
    }

    public function update(Request $request, Mapel $mapel)
    {
        $mapel->update($this->validasi($request, $mapel));

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui');
    }

    public function destroy(Mapel $mapel)
    {
        if ($mapel->penugasan()->exists()) {
            return back()->with('error', 'Mapel masih ditugaskan ke guru. Lepas penugasannya di menu Penugasan Guru terlebih dahulu.');
        }
        if ($mapel->nilais()->exists()) {
            return back()->with('error', 'Mapel sudah memiliki data nilai siswa sehingga tidak dapat dihapus.');
        }

        $mapel->delete();

        return back()->with('success', 'Mata pelajaran berhasil dihapus');
    }

    private function validasi(Request $request, ?Mapel $mapel = null): array
    {
        $id = $mapel?->id;

        // Kode & nama harus unik (tidak peduli huruf besar/kecil) → mencegah mapel duplikat
        $unik = function (string $kolom, string $label) use ($id) {
            return function ($attribute, $value, $fail) use ($kolom, $label, $id) {
                $ada = Mapel::whereRaw("LOWER({$kolom}) = ?", [mb_strtolower(trim($value))])
                            ->when($id, fn ($q) => $q->where('id', '!=', $id))
                            ->exists();
                if ($ada) $fail("{$label} sudah digunakan mata pelajaran lain.");
            };
        };

        return $request->validate([
            'kode'       => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9._-]+$/', $unik('kode', 'Kode')],
            'nama'       => ['required', 'string', 'max:100', $unik('nama', 'Nama')],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'kode.regex' => 'Kode hanya boleh berisi huruf, angka, titik, strip, dan garis bawah (tanpa spasi).',
        ]);
    }
}