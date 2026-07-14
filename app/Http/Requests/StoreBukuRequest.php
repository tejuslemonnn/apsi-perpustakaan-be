<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBukuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'isbn' => ['nullable', 'string', 'max:20', 'unique:buku,isbn'],
            'judul' => ['required', 'string', 'max:200'],
            'pengarang' => ['required', 'string', 'max:100'],
            'penerbit' => ['nullable', 'string', 'max:100'],
            'tahun_terbit' => ['nullable', 'integer', 'min:1000', 'max:9999'],
            'id_kategori' => ['nullable', 'integer', 'exists:kategori,id_kategori'],
            'stok' => ['required', 'integer', 'min:0'],
        ];
    }
}
