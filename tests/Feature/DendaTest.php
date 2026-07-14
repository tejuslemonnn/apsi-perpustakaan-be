<?php

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function setupFineScenario(): array
{
    $admin = User::create(['username' => 'admin1', 'password' => Hash::make('admin123'), 'role' => 'admin']);
    $u = User::create(['username' => 'nadia.putri', 'password' => Hash::make('anggota123'), 'role' => 'anggota']);
    $a = Anggota::create(['id_user' => $u->id_user, 'nama' => 'Nadia', 'tgl_daftar' => '2023-01-01']);
    $buku = Buku::create(['judul' => 'Test', 'pengarang' => 'X', 'stok' => 0]);
    $p = Peminjaman::create([
        'id_anggota' => $a->id_anggota,
        'tgl_pinjam' => '2026-05-10',
        'tgl_jatuh_tempo' => '2026-05-17',
        'status' => 'selesai',
    ]);
    $d = DetailPeminjaman::create([
        'id_peminjaman' => $p->id_peminjaman,
        'id_buku' => $buku->id_buku,
        'tgl_kembali' => '2026-05-22',
    ]);
    $denda = Denda::create([
        'id_detail' => $d->id_detail,
        'jumlah_hari' => 5,
        'jumlah_denda' => 5000,
        'status_bayar' => 'belum',
    ]);
    return compact('admin', 'u', 'a', 'buku', 'p', 'd', 'denda');
}

it('member sees their own fines in my-fines', function () {
    ['u' => $u, 'a' => $a, 'denda' => $denda] = setupFineScenario();

    $response = $this->actingAs($u, 'web')->get('/api/my-fines');
    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['jumlah_denda' => 5000.0]);
});

it('admin sees all fines in admin/fines', function () {
    ['admin' => $admin, 'denda' => $denda] = setupFineScenario();

    $response = $this->actingAs($admin, 'web')->get('/api/admin/fines');
    $response->assertOk();
    $response->assertJsonCount(1);
});

it('member can confirm payment (status -> menunggu_verifikasi)', function () {
    ['u' => $u, 'a' => $a, 'denda' => $denda] = setupFineScenario();

    $response = $this->actingAs($u, 'web')->post("/api/denda/{$denda->id_denda}/confirm-pay");
    $response->assertOk();
    expect($denda->fresh()->status_bayar)->toBe('menunggu_verifikasi');
});

it('admin can verify payment (status -> lunas, tgl_bayar set)', function () {
    ['admin' => $admin, 'denda' => $denda] = setupFineScenario();

    $response = $this->actingAs($admin, 'web')->post("/api/denda/{$denda->id_denda}/verify-pay");
    $response->assertOk();
    $dendaFresh = $denda->fresh();
    expect($dendaFresh->status_bayar)->toBe('lunas');
    expect($dendaFresh->tgl_bayar)->not->toBeNull();
});

it('non-admin cannot verify payment (403)', function () {
    ['u' => $u, 'denda' => $denda] = setupFineScenario();
    $response = $this->actingAs($u, 'web')->post("/api/denda/{$denda->id_denda}/verify-pay");
    $response->assertStatus(403);
});

it('cannot confirm pay already-lunas fine (422)', function () {
    ['admin' => $admin, 'u' => $u, 'a' => $a, 'denda' => $denda] = setupFineScenario();
    $denda->update(['status_bayar' => 'lunas', 'tgl_bayar' => '2026-05-25']);

    $response = $this->actingAs($u, 'web')->post("/api/denda/{$denda->id_denda}/confirm-pay");
    $response->assertStatus(422);
});
