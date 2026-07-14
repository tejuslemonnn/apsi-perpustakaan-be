<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $anggota = $this->route('anggota');
        $userId = $anggota ? $anggota->id_user : null;
        return [
            'nama' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'username' => ['nullable', 'string', 'max:50', 'unique:user,username,' . $userId . ',id_user'],
            'password' => ['nullable', 'string', 'min:6'],
        ];
    }
}
