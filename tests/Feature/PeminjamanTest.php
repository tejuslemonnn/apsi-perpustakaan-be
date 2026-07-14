<?php

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function anggotaUser(): User
{
    $u = User::create([
        'username' => 'nadia.putri',
        'password' => Hash::make('anggota123'),
        'role' => 'anggota',
    ]);
    $a = Anggota::create(['id_user' => $u->id_user, 'nama' => 'Nadia', 'tgl_daftar' => '2023-01-01']);
    $u->setRelation('anggota', $a);
    return $u;
}

beforeEach(function () {
    $this->buku = Buku::create(['judul' => 'Test Book', 'pengarang' => 'X', 'stok' => 3]);
    $this->anggota = anggotaUser();
});

it('member can borrow a book with stok', function () {
    $response = $this->actingAs($this->anggota, 'web')->post('/api/peminjaman', [
        'id_buku' => $this->buku->id_buku,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('peminjaman', ['id_anggota' => $this->anggota->anggota->id_anggota, 'status' => 'dipinjam']);
    expect($this->buku->fresh()->stok)->toBe(2);
});

it('member cannot borrow when stok is 0', function () {
    $this->buku->update(['stok' => 0]);

    $response = $this->actingAs($this->anggota, 'web')->post('/api/peminjaman', [
        'id_buku' => $this->buku->id_buku,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['id_buku']);
});

it('member can request return', function () {
    $p = Peminjaman::create([
        'id_anggota' => $this->anggota->anggota->id_anggota,
        'tgl_pinjam' => '2026-06-20',
        'tgl_jatuh_tempo' => '2026-06-27',
        'status' => 'dipinjam',
    ]);

    $response = $this->actingAs($this->anggota, 'web')->post("/api/peminjaman/{$p->id_peminjaman}/request-return");

    $response->assertOk();
    expect($p->fresh()->status)->toBe('menunggu_verifikasi');
});

it('member cannot request return for already returned loan', function () {
    $p = Peminjaman::create([
        'id_anggota' => $this->anggota->anggota->id_anggota,
        'tgl_pinjam' => '2026-06-20',
        'tgl_jatuh_tempo' => '2026-06-27',
        'status' => 'selesai',
    ]);

    $response = $this->actingAs($this->anggota, 'web')->post("/api/peminjaman/{$p->id_peminjaman}/request-return");

    $response->assertStatus(422);
});

it('admin can list all loans (via myLoans - empty for admin)', function () {
    $admin = User::create(['username' => 'admin1', 'password' => Hash::make('x'), 'role' => 'admin']);
    $response = $this->actingAs($admin, 'web')->get('/api/peminjaman/my');
    $response->assertOk();
});
