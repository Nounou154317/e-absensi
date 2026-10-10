<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ortu_id', 'nis', 'nama', 'kelas', 'jenis_kelamin', 'no_hp'];

    public function user() { return $this->belongsTo(User::class); }
    public function ortu() { return $this->belongsTo(User::class, 'ortu_id'); }

    public function absensis() { return $this->hasMany(Absensi::class); }
    public function nilais() {return $this->hasMany(Nilai::class); }
    public function pengajuanIzins() { return $this->hasMany(PengajuanIzin::class); }
    
}