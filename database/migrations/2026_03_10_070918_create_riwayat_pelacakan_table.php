<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('riwayat_pelacakan', function (Blueprint $table) {

            $table->id();

            // relasi ke tabel alumni
            $table->foreignId('alumni_id')
                  ->constrained('alumni')
                  ->onDelete('cascade');

            // query pencarian
            $table->string('query');

            // sumber data (linkedin, scholar, dll)
            $table->string('sumber');

            $table->string('jabatan')->nullable();

            $table->string('instansi')->nullable();

            $table->string('lokasi')->nullable();

            $table->string('bidang')->nullable();

            $table->year('tahun')->nullable();

            $table->string('link')->nullable();

            // nilai confidence hasil pencarian
            $table->integer('confidence_score')->default(0);

            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('riwayat_pelacakan');
    }

};