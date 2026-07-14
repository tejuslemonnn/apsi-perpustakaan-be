<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DendaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_denda' => $this->id_denda,
            'jumlah_hari' => $this->jumlah_hari,
            'jumlah_denda' => (float) $this->jumlah_denda,
            'status_bayar' => $this->status_bayar,
            'tgl_bayar' => $this->tgl_bayar?->format('Y-m-d'),
            'detail' => $this->whenLoaded('detailPeminjaman', function () {
                $d = $this->detailPeminjaman;
                return [
                    'id_detail' => $d->id_detail,
                    'tgl_kembali' => $d->tgl_kembali?->format('Y-m-d'),
                    'peminjaman' => $d->peminjaman ? [
                        'id_peminjaman' => $d->peminjaman->id_peminjaman,
                        'tgl_pinjam' => $d->peminjaman->tgl_pinjam?->format('Y-m-d'),
                        'tgl_jatuh_tempo' => $d->peminjaman->tgl_jatuh_tempo?->format('Y-m-d'),
                        'status' => $d->peminjaman->status,
                        'anggota' => $d->peminjaman->anggota ? [
                            'id_anggota' => $d->peminjaman->anggota->id_anggota,
                            'nama' => $d->peminjaman->anggota->nama,
                        ] : null,
                        'buku' => $d->buku ? [
                            'id_buku' => $d->buku->id_buku,
                            'judul' => $d->buku->judul,
                        ] : null,
                    ] : null,
                ];
            }),
        ];
    }
}
