<?php

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate', ['--seed' => false]);
});

function adminDashboard(): User
{
    return User::create([
        'username' => 'admin1',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);
}

it('returns dashboard stats for admin', function () {
    $admin = adminDashboard();
    Buku::create(['judul' => 'A', 'pengarang' => 'X', 'stok' => 1]);
    Buku::create(['judul' => 'B', 'pengarang' => 'Y', 'stok' => 1]);
    $u = User::create(['username' => 'n', 'password' => Hash::make('x'), 'role' => 'anggota']);
    $a = Anggota::create(['id_user' => $u->id_user, 'nama' => 'N', 'tgl_daftar' => '2023-01-01']);
    $p = Peminjaman::create(['id_anggota' => $a->id_anggota, 'tgl_pinjam' => '2023-01-01', 'tgl_jatuh_tempo' => '2023-01-08', 'status' => 'dipinjam']);
    DetailPeminjaman::create(['id_peminjaman' => $p->id_peminjaman, 'id_buku' => 1]);

    $response = $this->actingAs($admin, 'web')->get('/api/admin/dashboard');

    $response->assertOk();
    $response->assertJson([
        'totalBuku' => 2,
        'totalAnggota' => 1,
        'peminjamanAktif' => 1,
        'dendaBelumLunas' => 0,
    ]);
});

it('rejects non-admin from dashboard', function () {
    $u = User::create(['username' => 'n', 'password' => Hash::make('x'), 'role' => 'anggota']);
    $response = $this->actingAs($u, 'web')->get('/api/admin/dashboard');
    $response->assertStatus(403);
});

it('requires authentication for dashboard', function () {
    $response = $this->get('/api/admin/dashboard');
    $response->assertStatus(401);
});
