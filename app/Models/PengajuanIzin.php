<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PengajuanIzin extends Model
{
    protected $table = 'pengajuan_izins';

    protected $fillable = [
        'siswa_id', 'ortu_id', 'tanggal_mulai', 'tanggal_selesai',
        'jenis', 'alasan', 'foto_bukti', 'surat_dokter',
        'status', 'catatan_verifikasi', 'diverifikasi_oleh', 'diverifikasi_at',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'diverifikasi_at' => 'datetime',
    ];

    public function siswa()       { return $this->belongsTo(Siswa::class); }
    public function ortu()        { return $this->belongsTo(User::class, 'ortu_id'); }
    public function verifikator() { return $this->belongsTo(User::class, 'diverifikasi_oleh'); }
    public function absensis()    { return $this->hasMany(Absensi::class, 'pengajuan_id'); }

    /** Daftar hari sekolah (Senin–Jumat) pada rentang tanggal */
    public static function hariSekolah(Carbon $mulai, Carbon $selesai): array
    {
        $hasil = [];
        for ($d = $mulai->copy()->startOfDay(); $d->lte($selesai); $d->addDay()) {
            if ($d->isWeekday()) {
                $hasil[] = $d->copy();
            }
        }
        return $hasil;
    }

    public function jumlahHariSekolah(): int
    {
        return count(self::hariSekolah($this->tanggal_mulai, $this->tanggal_selesai));
    }

    public function getRentangTanggalAttribute(): string
    {
        if ($this->tanggal_mulai->isSameDay($this->tanggal_selesai)) {
            return $this->tanggal_mulai->translatedFormat('d M Y');
        }
        return $this->tanggal_mulai->translatedFormat('d M Y') . ' – ' . $this->tanggal_selesai->translatedFormat('d M Y');
    }
}