<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnggotaRequest;
use App\Http\Requests\UpdateAnggotaRequest;
use App\Http\Resources\AnggotaResource;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AnggotaController extends Controller
{
    public function index()
    {
        return AnggotaResource::collection(
            Anggota::with('user')->orderBy('nama')->get()
        );
    }

    public function store(StoreAnggotaRequest $request)
    {
        $data = $request->validated();

        $anggota = DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'role' => 'anggota',
            ]);
            return Anggota::create([
                'id_user' => $user->id_user,
                'nama' => $data['nama'],
                'alamat' => $data['alamat'] ?? null,
                'no_telp' => $data['no_telp'] ?? null,
                'email' => $data['email'] ?? null,
                'tgl_daftar' => $data['tgl_daftar'],
            ]);
        });

        $anggota->load('user');
        return new AnggotaResource($anggota);
    }

    public function show(Anggota $anggota)
    {
        $anggota->load('user');
        return new AnggotaResource($anggota);
    }

    public function update(UpdateAnggotaRequest $request, Anggota $anggota)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $anggota) {
            $anggota->update([
                'nama' => $data['nama'],
                'alamat' => $data['alamat'] ?? null,
                'no_telp' => $data['no_telp'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            $userUpdate = [];
            if (! empty($data['username'])) {
                $userUpdate['username'] = $data['username'];
            }
            if (! empty($data['password'])) {
                $userUpdate['password'] = Hash::make($data['password']);
            }
            if (! empty($userUpdate)) {
                $anggota->user->update($userUpdate);
            }
        });

        $anggota->load('user');
        return new AnggotaResource($anggota);
    }

    public function destroy(Anggota $anggota)
    {
        DB::transaction(function () use ($anggota) {
            $user = $anggota->user;
            $anggota->delete();
            if ($user) {
                $user->delete();
            }
        });
        return response()->noContent();
    }
}
