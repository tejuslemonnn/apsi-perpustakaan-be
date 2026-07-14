<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_peminjaman', function (Blueprint $table) {
            $table->increments('id_detail');
            $table->unsignedInteger('id_peminjaman');
            $table->unsignedInteger('id_buku');
            $table->date('tgl_kembali')->nullable();
            $table->timestamps();

            $table->foreign('id_peminjaman')
                ->references('id_peminjaman')->on('peminjaman')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('id_buku')
                ->references('id_buku')->on('buku')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_peminjaman');
    }
};
