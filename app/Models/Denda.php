<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    use HasFactory;

    protected $table = 'denda';
    protected $primaryKey = 'id_denda';

    protected $fillable = [
        'id_detail',
        'jumlah_hari',
        'jumlah_denda',
        'status_bayar',
        'tgl_bayar',
    ];

    protected $casts = [
        'jumlah_hari' => 'integer',
        'jumlah_denda' => 'decimal:2',
        'tgl_bayar' => 'date',
    ];

    public function detailPeminjaman()
    {
        return $this->belongsTo(DetailPeminjaman::class, 'id_detail', 'id_detail');
    }
}
