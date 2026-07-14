<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->increments('id_peminjaman');
            $table->unsignedInteger('id_anggota');
            $table->date('tgl_pinjam');
            $table->date('tgl_jatuh_tempo');
            // 3-state enum: dipinjam -> menunggu_verifikasi -> selesai
            $table->enum('status', ['dipinjam', 'menunggu_verifikasi', 'selesai'])->default('dipinjam');
            $table->timestamps();

            $table->foreign('id_anggota')
                ->references('id_anggota')->on('anggota')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
