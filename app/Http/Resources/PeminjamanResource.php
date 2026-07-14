<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_peminjaman' => $this->id_peminjaman,
            'tgl_pinjam' => $this->tgl_pinjam?->format('Y-m-d'),
            'tgl_jatuh_tempo' => $this->tgl_jatuh_tempo?->format('Y-m-d'),
            'status' => $this->status,
            'anggota' => $this->whenLoaded('anggota', function () {
                return [
                    'id_anggota' => $this->anggota->id_anggota,
                    'nama' => $this->anggota->nama,
                ];
            }),
            'items' => $this->whenLoaded('detailPeminjaman', function () {
                return $this->detailPeminjaman->map(function ($detail) {
                    return [
                        'id_detail' => $detail->id_detail,
                        'tgl_kembali' => $detail->tgl_kembali?->format('Y-m-d'),
                        'buku' => $detail->buku ? [
                            'id_buku' => $detail->buku->id_buku,
                            'judul' => $detail->buku->judul,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
