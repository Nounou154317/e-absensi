<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class GuruMapel extends Model
{
    protected $table = 'guru_mata_pelajaran';

    protected $fillable = ['guru_id', 'mapel_id', 'kelas'];

    const MAKS_MAPEL = 3;

    protected static function booted(): void
    {
        // Guard terakhir di level model: guru tidak boleh punya > 3 mapel berbeda,
        // siapa pun/apa pun yang menyimpan datanya (controller, seeder, tinker).
        static::creating(function (GuruMapel $row) {
            $sudahAda = static::where('guru_id', $row->guru_id)
                              ->where('mapel_id', $row->mapel_id)->exists();

            if (!$sudahAda) {
                $jumlah = static::where('guru_id', $row->guru_id)->distinct()->count('mapel_id');
                if ($jumlah >= self::MAKS_MAPEL) {
                    throw ValidationException::withMessages([
                        'penugasan' => 'Guru tidak boleh memiliki lebih dari ' . self::MAKS_MAPEL . ' mata pelajaran.',
                    ]);
                }
            }
        });
    }

    public function guru()  { return $this->belongsTo(User::class, 'guru_id'); }
    public function mapel() { return $this->belongsTo(Mapel::class); }
}