<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->increments('id_buku');
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('judul', 200);
            $table->string('pengarang', 100);
            $table->string('penerbit', 100)->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->unsignedInteger('id_kategori')->nullable();
            $table->integer('stok')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('id_kategori')
                ->references('id_kategori')->on('kategori')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE buku ADD CONSTRAINT membatasi_stok CHECK (stok >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
