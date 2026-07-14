<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';
    protected $primaryKey = 'id_peminjaman';

    protected $fillable = [
        'id_anggota',
        'tgl_pinjam',
        'tgl_jatuh_tempo',
        'status',
    ];

    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_jatuh_tempo' => 'date',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }

    public function detailPeminjaman()
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function denda()
    {
        return $this->hasManyThrough(Denda::class, DetailPeminjaman::class, 'id_peminjaman', 'id_detail', 'id_peminjaman', 'id_detail');
    }
}
