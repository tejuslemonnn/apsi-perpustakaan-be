<?php

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function makeAdminUser(): User
{
    return User::create([
        'username' => 'admin1',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);
}

function makeAnggotaWithLoan(array $overrides = []): array
{
    $u = User::create([
        'username' => 'nadia.putri',
        'password' => Hash::make('anggota123'),
        'role' => 'anggota',
    ]);
    $a = Anggota::create(['id_user' => $u->id_user, 'nama' => 'Nadia', 'tgl_daftar' => '2023-01-01']);
    $buku = Buku::create(['judul' => 'Test', 'pengarang' => 'X', 'stok' => 1]);
    $buku->update(['stok' => 0]);

    $p = Peminjaman::create(array_merge([
        'id_anggota' => $a->id_anggota,
        'tgl_pinjam' => Carbon::today()->subDays(10),
        'tgl_jatuh_tempo' => Carbon::today()->subDays(3),
        'status' => 'dipinjam',
    ], $overrides));

    DetailPeminjaman::create([
        'id_peminjaman' => $p->id_peminjaman,
        'id_buku' => $buku->id_buku,
    ]);

    return [$p, $a, $buku, $u];
}

it('admin can process return on time (no fine)', function () {
    $admin = makeAdminUser();
    [$p, $a, $buku, $u] = makeAnggotaWithLoan([
        'tgl_pinjam' => Carbon::today()->subDays(3),
        'tgl_jatuh_tempo' => Carbon::today()->addDays(4),
        'status' => 'dipinjam',
    ]);

    $response = $this->actingAs($admin, 'web')->post("/api/peminjaman/{$p->id_peminjaman}/process-return");
    $response->assertOk();
    $response->assertJson(['lateDays' => 0, 'fineAmount' => 0]);
    expect($p->fresh()->status)->toBe('selesai');
    expect($buku->fresh()->stok)->toBe(1);
});

it('admin can process late return and create fine', function () {
    $admin = makeAdminUser();
    [$p, $a, $buku, $u] = makeAnggotaWithLoan([
        'tgl_pinjam' => Carbon::today()->subDays(10),
        'tgl_jatuh_tempo' => Carbon::today()->subDays(3),
        'status' => 'dipinjam',
    ]);

    $response = $this->actingAs($admin, 'web')->post("/api/peminjaman/{$p->id_peminjaman}/process-return");
    $response->assertOk();
    $response->assertJson(['lateDays' => 3, 'fineAmount' => 3000]);
    expect($p->fresh()->status)->toBe('selesai');
    $this->assertDatabaseHas('denda', ['jumlah_hari' => 3, 'jumlah_denda' => 3000, 'status_bayar' => 'belum']);
});

it('admin cannot process already returned loan', function () {
    $admin = makeAdminUser();
    [$p, $a, $buku, $u] = makeAnggotaWithLoan(['status' => 'selesai']);
    $response = $this->actingAs($admin, 'web')->post("/api/peminjaman/{$p->id_peminjaman}/process-return");
    $response->assertStatus(422);
});

it('admin can list all loans', function () {
    $admin = makeAdminUser();
    makeAnggotaWithLoan();

    $response = $this->actingAs($admin, 'web')->get('/api/admin/loans');
    $response->assertOk();
    $response->assertJsonCount(1);
});

it('admin can search loans by anggota nama', function () {
    $admin = makeAdminUser();
    makeAnggotaWithLoan();

    $response = $this->actingAs($admin, 'web')->get('/api/admin/loans?q=Nadia');
    $response->assertOk();
    $response->assertJsonCount(1);

    $response = $this->actingAs($admin, 'web')->get('/api/admin/loans?q=NonExistent');
    $response->assertOk();
    $response->assertJsonCount(0);
});

it('non-admin cannot process return', function () {
    [$p, $a, $buku, $u] = makeAnggotaWithLoan();
    $response = $this->actingAs($u, 'web')->post("/api/peminjaman/{$p->id_peminjaman}/process-return");
    $response->assertStatus(403);
});
