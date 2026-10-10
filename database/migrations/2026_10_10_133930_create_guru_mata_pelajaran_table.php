<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Keterangan mata pelajaran (kode & nama sudah ada)
        Schema::table('mapels', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('kode');
        });

        // 2. Pivot penugasan: guru ↔ mapel ↔ kelas
        Schema::create('guru_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mapels')->cascadeOnDelete();
            $table->string('kelas', 50);
            $table->timestamps();

            $table->unique(['guru_id', 'mapel_id', 'kelas']);
        });

        // 3. Backfill agar guru lama tidak kehilangan akses:
        //    guru yang kolom users.mata_pelajaran-nya cocok dengan nama mapel
        //    otomatis ditugaskan ke semua kelas yang ada (bila mapel+kelas itu belum diambil guru lain).
        $kelasList = DB::table('siswas')->distinct()->pluck('kelas');
        $gurus = DB::table('users')->where('role', 'guru')->whereNotNull('mata_pelajaran')
                    ->get(['id', 'mata_pelajaran']);

        foreach ($gurus as $g) {
            $mapel = DB::table('mapels')
                ->whereRaw('LOWER(nama) = ?', [mb_strtolower(trim($g->mata_pelajaran))])
                ->first();
            if (!$mapel) continue;

            foreach ($kelasList as $kelas) {
                $dipakai = DB::table('guru_mata_pelajaran')
                    ->where('mapel_id', $mapel->id)->where('kelas', $kelas)->exists();
                if ($dipakai) continue;

                DB::table('guru_mata_pelajaran')->insert([
                    'guru_id'    => $g->id,
                    'mapel_id'   => $mapel->id,
                    'kelas'      => $kelas,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_mata_pelajaran');
        Schema::table('mapels', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};