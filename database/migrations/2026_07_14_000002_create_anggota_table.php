<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->increments('id_anggota');
            $table->unsignedInteger('id_user')->unique();
            $table->string('nama', 100);
            $table->string('alamat', 255)->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->date('tgl_daftar');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')->on('user')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
