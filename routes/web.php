<?php

use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\PenugasanGuruController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\SiswaController;

use App\Http\Controllers\Guru\PengajuanController;

use Illuminate\Support\Facades\Route;

// ===== AUTH =====
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin'     => redirect()->route('admin.dashboard'),
            'guru'      => redirect()->route('guru.dashboard'),
            'siswa'     => redirect()->route('siswa.dashboard'),
            'orang_tua' => redirect()->route('orang_tua.dashboard'),
            default     => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// ===== BERKAS PENGAJUAN (privat; hak akses dicek di controller) =====
Route::get('/berkas/pengajuan/{pengajuan}/{jenis}', [BerkasController::class, 'pengajuan'])
    ->whereIn('jenis', ['foto', 'surat'])
    ->middleware('auth')
    ->name('berkas.pengajuan');

// ===== ADMIN =====
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Kelola Siswa
    Route::get('/siswa', [AdminController::class, 'siswa'])->name('siswa');
    Route::get('/siswa/create', [AdminController::class, 'siswaCreate'])->name('siswa.create');
    Route::post('/siswa', [AdminController::class, 'siswaStore'])->name('siswa.store');

    // Kelola Mata Pelajaran (master data)
    Route::resource('mapel', MapelController::class)->except(['show']);

    // Penugasan Guru ↔ Mata Pelajaran ↔ Kelas
    Route::get('/penugasan-guru', [PenugasanGuruController::class, 'index'])->name('penugasan.index');
    Route::get('/penugasan-guru/{guru}/edit', [PenugasanGuruController::class, 'edit'])->name('penugasan.edit');
    Route::put('/penugasan-guru/{guru}', [PenugasanGuruController::class, 'update'])->name('penugasan.update');

    // Kelola User
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'userCreate'])->name('users.create');
    Route::post('/users', [AdminController::class, 'userStore'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'userEdit'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'userUpdate'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'userDestroy'])->name('users.destroy');

    Route::get('/absensi', [AdminController::class, 'absensi'])->name('absensi');
});

// ===== GURU =====
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruController::class, 'dashboard'])->name('dashboard');
    Route::get('/penugasan/live', [GuruController::class, 'penugasanLive'])->name('penugasan.live');
    Route::get('/rekap', [GuruController::class, 'rekap'])->name('rekap');
    Route::put('/absensi/{absensi}', [GuruController::class, 'updateAbsensi'])->name('absensi.update');

    Route::get('/nilai', [\App\Http\Controllers\Guru\NilaiController::class, 'index'])->name('nilai.index');
    Route::get('/nilai/{siswa}/edit', [\App\Http\Controllers\Guru\NilaiController::class, 'edit'])->name('nilai.edit');
    Route::put('/nilai/{siswa}', [\App\Http\Controllers\Guru\NilaiController::class, 'update'])->name('nilai.update');
    Route::get('/nilai-akhir', [\App\Http\Controllers\Guru\NilaiController::class, 'rekapAkhir'])->name('nilai.akhir');
    Route::post('/nilai/bulk', [\App\Http\Controllers\Guru\NilaiController::class, 'bulkUpdate'])->name('nilai.bulk');

    // Verifikasi pengajuan izin/sakit dari orang tua
    Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::post('/pengajuan/{pengajuan}/setujui', [PengajuanController::class, 'setujui'])->name('pengajuan.setujui');
    Route::post('/pengajuan/{pengajuan}/tolak', [PengajuanController::class, 'tolak'])->name('pengajuan.tolak');
});

// ===== SISWA =====
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaController::class, 'dashboard'])->name('dashboard');
    Route::post('/absen-masuk', [SiswaController::class, 'absenMasuk'])->name('absen.masuk');
    Route::post('/absen-pulang', [SiswaController::class, 'absenPulang'])->name('absen.pulang');
    Route::get('/riwayat', [SiswaController::class, 'riwayat'])->name('riwayat');
    Route::get('/riwayat/live', [SiswaController::class, 'riwayatLive'])->name('riwayat.live');
    Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai');
});

// ===== ORANG TUA SISWA =====
Route::middleware(['auth', 'role:orang_tua'])->prefix('orang-tua')->name('orang_tua.')->group(function () {
    Route::get('/dashboard', [OrangTuaController::class, 'dashboard'])->name('dashboard');
    Route::get('/kalender', [OrangTuaController::class, 'kalender'])->name('kalender');
    Route::get('/izin', [OrangTuaController::class, 'izinForm'])->name('izin');
    Route::post('/izin', [OrangTuaController::class, 'izinStore'])->name('izin.store');
    Route::delete('/izin/{pengajuan}', [OrangTuaController::class, 'izinBatal'])->name('izin.batal');
});