<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\User;
use App\Models\GuruMapel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalSiswa = Siswa::count();
        $totalGuru = User::where('role', 'guru')->count();
        $absensiHariIni = Absensi::whereDate('tanggal', today())->count();

        return view('admin.dashboard', compact('totalSiswa', 'totalGuru', 'absensiHariIni'));
    }

    // ============ KELOLA SISWA ============
    public function siswa(Request $request)
    {
        $keyword = $request->input('q') ?? $request->input('search');

        $siswas = Siswa::with('user')
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($w) use ($keyword) {
                    $w->where('nama', 'like', '%' . $keyword . '%')
                      ->orWhere('nis', 'like', '%' . $keyword . '%');
                });
            })
            ->when($request->filled('kelas'), fn($q) => $q->where('kelas', $request->kelas))
            ->orderBy('kelas')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        $kelasList = Siswa::distinct()->orderBy('kelas')->pluck('kelas');

        return view('admin.siswa', compact('siswas', 'kelasList'));
    }

    public function siswaCreate()
    {
        $kelasList = Siswa::distinct()->orderBy('kelas')->pluck('kelas');

        return view('admin.siswa.create', compact('kelasList'));
    }

    public function siswaStore(Request $request)
    {
        $data = $request->validate([
            'nama'          => 'required|string|max:255',
            'nis'           => 'required|string|max:30|unique:users,nis|unique:siswas,nis',
            'kelas'         => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp'         => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:6|confirmed',
        ], [
            'nis.unique'         => 'NIS sudah terdaftar.',
            'email.unique'       => 'Email sudah digunakan.',
            'no_hp.regex'        => 'No. HP hanya boleh berisi angka, spasi, + dan -.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['nama'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'siswa',
                'nis'      => $data['nis'],
                'kelas'    => $data['kelas'],
            ]);

            Siswa::create([
                'user_id'       => $user->id,
                'nis'           => $data['nis'],
                'nama'          => $data['nama'],
                'kelas'         => $data['kelas'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'no_hp'         => $data['no_hp'] ?? null,
            ]);
        });

        return redirect()->route('admin.siswa')->with('success', 'Siswa berhasil ditambahkan');
    }

    // ============ KELOLA USER ============
    public function users()
    {
        $users = User::with('anak', 'penugasan')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function userCreate()
    {
        $siswaList = Siswa::with('ortu')->orderBy('kelas')->orderBy('nama')->get();
        return view('admin.users.create', compact('siswaList'));
    }

    public function userStore(Request $request)
    {
        $rules = [
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,guru,siswa,orang_tua',
        ];

        if ($request->role === 'siswa') {
            $rules['nis']           = 'required|unique:users,nis';
            $rules['kelas']         = 'required';
            $rules['jenis_kelamin'] = 'required|in:L,P';
        }

        if ($request->role === 'orang_tua') {
            $rules['anak_ids']   = 'required|array|min:1';
            $rules['anak_ids.*'] = 'exists:siswas,id';
        }

        $data = $request->validate($rules);
        $data['password'] = bcrypt($data['password']);

        $user = User::create(Arr::except($data, ['anak_ids', 'jenis_kelamin']));

        if ($user->role === 'siswa') {
            Siswa::create([
                'user_id'       => $user->id,
                'nis'           => $user->nis,
                'nama'          => $user->name,
                'kelas'         => $user->kelas,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);
        }

        if ($user->role === 'orang_tua') {
            Siswa::whereIn('id', $data['anak_ids'])->update(['ortu_id' => $user->id]);
        }

        if ($user->role === 'guru') {
            return redirect()->route('admin.penugasan.edit', $user)
                ->with('success', 'Akun guru dibuat. Tetapkan 1–3 mata pelajaran dan kelasnya.');
        }

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan');
    }

    public function userEdit(User $user)
    {
        $siswaList = Siswa::with('ortu')->orderBy('kelas')->orderBy('nama')->get();
        return view('admin.users.edit', compact('user', 'siswaList'));
    }

    public function userUpdate(Request $request, User $user)
    {
        $rules = [
            'name'  => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|in:admin,guru,siswa,orang_tua',
        ];

        if ($request->role === 'siswa') {
            $rules['nis']   = 'required|unique:users,nis,' . $user->id;
            $rules['kelas'] = 'required';
        }

        if ($request->role === 'orang_tua') {
            $rules['anak_ids']   = 'required|array|min:1';
            $rules['anak_ids.*'] = 'exists:siswas,id';
        }

        $data = $request->validate($rules);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update(Arr::except($data, ['anak_ids']));

        if ($user->role !== 'guru') {
            GuruMapel::where('guru_id', $user->id)->delete();
        }

        // Update / buat data siswa terkait
        if ($user->role === 'siswa') {
            Siswa::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nis'           => $user->nis,
                    'nama'          => $user->name,
                    'kelas'         => $user->kelas,
                    'jenis_kelamin' => $request->jenis_kelamin ?? 'L',
                ]
            );
        }

        // Sinkronkan anak yang terhubung dengan akun orang tua
        Siswa::where('ortu_id', $user->id)->update(['ortu_id' => null]);
        if ($user->role === 'orang_tua') {
            Siswa::whereIn('id', $data['anak_ids'])->update(['ortu_id' => $user->id]);
        }

        return redirect()->route('admin.users')->with('success', 'User berhasil diupdate');
    }

    public function userDestroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri');
        }

        // Lepas hubungan anak lebih dulu
        Siswa::where('ortu_id', $user->id)->update(['ortu_id' => null]);

        $user->delete();
        return back()->with('success', 'User berhasil dihapus');
    }

    // ============ LIHAT SEMUA ABSENSI ============
    public function absensi(Request $request)
    {
        $query = Absensi::with('siswa')->latest('tanggal');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('kelas')) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas', $request->kelas));
        }

        $absensis = $query->paginate(15)->withQueryString();

        $kelasList = Siswa::distinct()->orderBy('kelas')->pluck('kelas');

        return view('admin.absensi', compact('absensis', 'kelasList'));
    }
}