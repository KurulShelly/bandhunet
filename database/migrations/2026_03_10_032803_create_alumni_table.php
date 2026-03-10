<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('prodi');
            $table->year('tahun_lulus');
            $table->string('kota')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('instansi')->nullable();
            $table->enum('status_pelacakan', [
                'Belum Dilacak',
                'Teridentifikasi',
                'Perlu Verifikasi',
                'Tidak Ditemukan'
            ])->default('Belum Dilacak');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
