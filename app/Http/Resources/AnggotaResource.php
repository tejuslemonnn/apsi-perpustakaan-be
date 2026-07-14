<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnggotaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_anggota' => $this->id_anggota,
            'nama' => $this->nama,
            'alamat' => $this->alamat,
            'no_telp' => $this->no_telp,
            'email' => $this->email,
            'tgl_daftar' => $this->tgl_daftar?->format('Y-m-d'),
            'username' => $this->whenLoaded('user', fn () => $this->user->username),
        ];
    }
}
