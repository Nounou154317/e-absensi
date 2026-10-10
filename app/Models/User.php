<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'nis', 'kelas', 'mata_pelajaran',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function anak()
    {
        return $this->hasMany(Siswa::class, 'ortu_id');
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isGuru(): bool  { return $this->role === 'guru'; }
    public function isSiswa(): bool { return $this->role === 'siswa'; }
    public function isOrangTua(): bool { return $this->role === 'orang_tua'; }
    // =====================================================
    //  PENUGASAN GURU (mapel + kelas)
    // =====================================================

    /** Baris penugasan (guru–mapel–kelas) milik guru ini */
    public function penugasan()
    {
        return $this->hasMany(GuruMapel::class, 'guru_id');
    }

    /** Mata pelajaran yang diampu (tanpa duplikat) */
    public function mapels()
    {
        return $this->belongsToMany(Mapel::class, 'guru_mata_pelajaran', 'guru_id', 'mapel_id')->distinct();
    }

    public function mapelDiampu(): Collection
    {
        return $this->mapels()->orderBy('nama')->get();
    }

    /** Kelas yang diajar (opsional: untuk 1 mapel saja) */
    public function kelasDiampu(?int $mapelId = null): Collection
    {
        return $this->penugasan()
            ->when($mapelId, fn ($q) => $q->where('mapel_id', $mapelId))
            ->orderBy('kelas')->pluck('kelas')->unique()->values();
    }

    /** Boleh mengelola mapel ini di kelas ini? */
    public function bolehMengajar(int $mapelId, string $kelas): bool
    {
        return $this->penugasan()->where('mapel_id', $mapelId)->where('kelas', $kelas)->exists();
    }

    /** Boleh melihat siswa di kelas ini? (mengajar minimal 1 mapel di kelas tsb) */
    public function bolehKelas(?string $kelas): bool
    {
        return $kelas !== null && $this->penugasan()->where('kelas', $kelas)->exists();
    }

    /** Sidik jari penugasan: berubah jika admin mengubah penugasan (dipakai untuk auto-refresh dashboard) */
    public function hashPenugasan(): string
    {
        return md5($this->penugasan()->orderBy('mapel_id')->orderBy('kelas')
            ->get(['mapel_id', 'kelas'])->toJson());
    }

    /** Ringkasan untuk dashboard guru */
    public function ringkasanPenugasan(): array
    {
        $rows = $this->penugasan()->with('mapel')->get();

        $perMapel = $rows->groupBy('mapel_id')->map(function ($g) {
            $kelas = $g->pluck('kelas')->sort()->values();
            return [
                'mapel'         => $g->first()->mapel,
                'kelas'         => $kelas->all(),
                'jumlah_siswa'  => Siswa::whereIn('kelas', $kelas)->count(),
            ];
        })->sortBy(fn ($x) => $x['mapel']->nama)->values();

        $kelas = $rows->pluck('kelas')->unique()->sort()->values();

        return [
            'per_mapel'    => $perMapel,
            'jumlah_mapel' => $perMapel->count(),
            'kelas'        => $kelas,
            'jumlah_kelas' => $kelas->count(),
            'jumlah_siswa' => $kelas->isEmpty() ? 0 : Siswa::whereIn('kelas', $kelas)->count(),
        ];
    }
}