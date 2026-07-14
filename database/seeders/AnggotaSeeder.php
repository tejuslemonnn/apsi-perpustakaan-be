<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AnggotaSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['nama' => 'Nadia Putri Ramadhani', 'alamat' => 'Jl. Kenanga No. 12, Bandung', 'telp' => '0812-3456-7801', 'email' => 'nadia.putri@mail.com', 'tgl_daftar' => '2023-02-10', 'username' => 'nadia.putri'],
            ['nama' => 'Rizky Ahmad Fauzan', 'alamat' => 'Jl. Merdeka No. 5, Bandung', 'telp' => '0813-2211-4455', 'email' => 'rizky.fauzan@mail.com', 'tgl_daftar' => '2023-03-01', 'username' => 'rizky.fauzan'],
            ['nama' => 'Sari Wulandari', 'alamat' => 'Jl. Dipatiukur No. 22, Bandung', 'telp' => '0857-1122-3344', 'email' => 'sari.wulan@mail.com', 'tgl_daftar' => '2023-03-15', 'username' => 'sari.wulan'],
            ['nama' => 'Bagus Prasetyo', 'alamat' => 'Jl. Sukajadi No. 8, Bandung', 'telp' => '0821-9988-1122', 'email' => 'bagus.p@mail.com', 'tgl_daftar' => '2023-04-02', 'username' => 'bagus.p'],
            ['nama' => 'Dewi Anggraini', 'alamat' => 'Jl. Cihampelas No. 41, Bandung', 'telp' => '0812-6677-8899', 'email' => 'dewi.anggraini@mail.com', 'tgl_daftar' => '2023-04-20', 'username' => 'dewi.anggraini'],
            ['nama' => 'Fajar Nugroho', 'alamat' => 'Jl. Pasteur No. 3, Bandung', 'telp' => '0838-4455-1231', 'email' => 'fajar.n@mail.com', 'tgl_daftar' => '2023-05-05', 'username' => 'fajar.n'],
            ['nama' => 'Melati Kusuma', 'alamat' => 'Jl. Ir. H. Juanda No. 19, Bandung', 'telp' => '0812-7766-5544', 'email' => 'melati.k@mail.com', 'tgl_daftar' => '2023-05-18', 'username' => 'melati.k'],
            ['nama' => 'Andra Wibisono', 'alamat' => 'Jl. Setiabudi No. 55, Bandung', 'telp' => '0857-3344-2211', 'email' => 'andra.w@mail.com', 'tgl_daftar' => '2023-06-01', 'username' => 'andra.w'],
            ['nama' => 'Citra Puspita', 'alamat' => 'Jl. Riau No. 88, Bandung', 'telp' => '0813-5566-7788', 'email' => 'citra.p@mail.com', 'tgl_daftar' => '2023-06-14', 'username' => 'citra.p'],
            ['nama' => 'Doni Setiawan', 'alamat' => 'Jl. Buah Batu No. 30, Bandung', 'telp' => '0821-1234-5678', 'email' => 'doni.s@mail.com', 'tgl_daftar' => '2023-07-02', 'username' => 'doni.s'],
            ['nama' => 'Intan Permatasari', 'alamat' => 'Jl. Ahmad Yani No. 14, Bandung', 'telp' => '0812-9900-1122', 'email' => 'intan.p@mail.com', 'tgl_daftar' => '2023-07-19', 'username' => 'intan.p'],
            ['nama' => 'Yusuf Maulana', 'alamat' => 'Jl. Antapani No. 6, Bandung', 'telp' => '0838-2233-4455', 'email' => 'yusuf.m@mail.com', 'tgl_daftar' => '2023-08-08', 'username' => 'yusuf.m'],
            ['nama' => 'Putri Amelia', 'alamat' => 'Jl. Kopo No. 100, Bandung', 'telp' => '0857-6655-4433', 'email' => 'putri.amelia@mail.com', 'tgl_daftar' => '2023-08-22', 'username' => 'putri.amelia'],
            ['nama' => 'Galih Saputra', 'alamat' => 'Jl. Cibiru No. 17, Bandung', 'telp' => '0812-4455-6677', 'email' => 'galih.s@mail.com', 'tgl_daftar' => '2023-09-09', 'username' => 'galih.s'],
            ['nama' => 'Wahyu Hidayat', 'alamat' => 'Jl. Ujungberung No. 9, Bandung', 'telp' => '0813-8899-0011', 'email' => 'wahyu.h@mail.com', 'tgl_daftar' => '2023-09-25', 'username' => 'wahyu.h'],
            ['nama' => 'Ratna Sari Dewi', 'alamat' => 'Jl. Gedebage No. 21, Bandung', 'telp' => '0821-5566-7799', 'email' => 'ratna.sari@mail.com', 'tgl_daftar' => '2023-10-10', 'username' => 'ratna.sari'],
        ];

        foreach ($members as $m) {
            $user = User::updateOrCreate(
                ['username' => $m['username']],
                [
                    'password' => Hash::make('anggota123'),
                    'role' => 'anggota',
                ]
            );
            Anggota::updateOrCreate(
                ['id_user' => $user->id_user],
                [
                    'nama' => $m['nama'],
                    'alamat' => $m['alamat'],
                    'no_telp' => $m['telp'],
                    'email' => $m['email'],
                    'tgl_daftar' => $m['tgl_daftar'],
                ]
            );
        }
    }
}
