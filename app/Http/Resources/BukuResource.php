<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_buku' => $this->id_buku,
            'isbn' => $this->isbn,
            'judul' => $this->judul,
            'pengarang' => $this->pengarang,
            'penerbit' => $this->penerbit,
            'tahun_terbit' => $this->tahun_terbit,
            'stok' => $this->stok,
            'kategori' => $this->whenLoaded('kategori', function () {
                return [
                    'id_kategori' => $this->kategori->id_kategori,
                    'nama_kategori' => $this->kategori->nama_kategori,
                ];
            }),
        ];
    }
}
