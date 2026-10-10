<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Role: enum lama (admin/guru/siswa) → string agar bisa menampung 'orang_tua'
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('siswa')->change();
        });

        // 2. Hubungan orang tua → anak (1 orang tua bisa punya banyak anak)
        Schema::table('siswas', function (Blueprint $table) {
            $table->foreignId('ortu_id')->nullable()->after('user_id')
                  ->constrained('users')->nullOnDelete();
        });

        // 3. Pengajuan izin / sakit dari orang tua
        Schema::create('pengajuan_izins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('ortu_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('jenis', 10);                       // izin | sakit
            $table->text('alasan');
            $table->string('foto_bukti');                      // wajib
            $table->string('surat_dokter')->nullable();        // wajib untuk sakit
            $table->string('status', 15)->default('menunggu'); // menunggu | disetujui | ditolak
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
        });

        // 4. Absensi hasil persetujuan menyimpan referensi pengajuan
        Schema::table('absensis', function (Blueprint $table) {
            $table->foreignId('pengajuan_id')->nullable()->after('keterangan')
                  ->constrained('pengajuan_izins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pengajuan_id');
        });
        Schema::dropIfExists('pengajuan_izins');
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ortu_id');
        });
    }
};