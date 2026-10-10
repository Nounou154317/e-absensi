<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    protected $fillable = ['nama', 'kode', 'keterangan'];

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }
    /** Penugasan (guru + kelas) untuk mapel ini */
    public function penugasan()
    {
        return $this->hasMany(GuruMapel::class);
    }

    /** Guru yang mengajar mapel ini (tanpa duplikat) */
    public function gurus()
    {
        return $this->belongsToMany(User::class, 'guru_mata_pelajaran', 'mapel_id', 'guru_id')->distinct();
    }

    public function getLabelAttribute(): string
    {
        return $this->kode . ' — ' . $this->nama;
    }
}