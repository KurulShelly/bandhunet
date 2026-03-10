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

            $table->foreignId('alumni_id');

            $table->string('sumber');

            $table->string('jabatan')->nullable();

            $table->string('instansi')->nullable();

            $table->integer('confidence_score');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pelacakan');
    }
};