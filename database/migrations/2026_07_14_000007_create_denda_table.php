<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('denda', function (Blueprint $table) {
            $table->increments('id_denda');
            // PER-ITEM FK: each detail_peminjaman has at most 1 denda
            $table->unsignedInteger('id_detail')->unique();
            $table->integer('jumlah_hari')->default(0);
            $table->decimal('jumlah_denda', 10, 2)->default(0);
            // 3-state: belum -> menunggu_verifikasi -> lunas
            $table->enum('status_bayar', ['belum', 'menunggu_verifikasi', 'lunas'])->default('belum');
            $table->date('tgl_bayar')->nullable();
            $table->timestamps();

            $table->foreign('id_detail')
                ->references('id_detail')->on('detail_peminjaman')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denda');
    }
};
