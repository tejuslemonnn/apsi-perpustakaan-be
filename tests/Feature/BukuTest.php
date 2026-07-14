<?php

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function adminBuku(): User
{
    return User::create([
        'username' => 'admin1',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);
}

function anggotaBuku(): User
{
    return User::create([
        'username' => 'nadia.putri',
        'password' => Hash::make('anggota123'),
        'role' => 'anggota',
    ]);
}

beforeEach(function () {
    $this->kategori = Kategori::create(['nama_kategori' => 'Fiksi']);
});

it('lists all buku', function () {
    Buku::create(['judul' => 'Laut Bercerita', 'pengarang' => 'Leila', 'stok' => 3]);
    Buku::create(['judul' => 'Bumi Manusia', 'pengarang' => 'Pram', 'stok' => 0]);

    $admin = adminBuku();
    $response = $this->actingAs($admin, 'web')->get('/api/buku');

    $response->assertOk();
    $response->assertJsonCount(2);
});

it('searches buku by judul', function () {
    Buku::create(['judul' => 'Laut Bercerita', 'pengarang' => 'Leila', 'stok' => 3]);
    Buku::create(['judul' => 'Bumi Manusia', 'pengarang' => 'Pram', 'stok' => 0]);

    $admin = adminBuku();
    $response = $this->actingAs($admin, 'web')->get('/api/buku?q=Laut');

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['judul' => 'Laut Bercerita']);
});

it('sorts buku by judul asc', function () {
    Buku::create(['judul' => 'Zebra', 'pengarang' => 'A', 'stok' => 1]);
    Buku::create(['judul' => 'Apple', 'pengarang' => 'B', 'stok' => 1]);

    $admin = adminBuku();
    $response = $this->actingAs($admin, 'web')->get('/api/buku?sort=judul&direction=asc');

    $response->assertOk();
    $data = $response->json();
    expect($data[0]['judul'])->toBe('Apple');
    expect($data[1]['judul'])->toBe('Zebra');
});

it('creates a buku with kategori', function () {
    $admin = adminBuku();
    $response = $this->actingAs($admin, 'web')->post('/api/buku', [
        'judul' => 'Laut Bercerita',
        'pengarang' => 'Leila S. Chudori',
        'isbn' => '978-602-424-101-1',
        'penerbit' => 'KPG',
        'tahun_terbit' => 2017,
        'id_kategori' => $this->kategori->id_kategori,
        'stok' => 3,
    ]);

    $response->assertCreated();
    $response->assertJson(['judul' => 'Laut Bercerita', 'stok' => 3]);
    $this->assertDatabaseHas('buku', ['judul' => 'Laut Bercerita', 'stok' => 3]);
});

it('rejects buku without required fields', function () {
    $admin = adminBuku();
    $response = $this->actingAs($admin, 'web')->post('/api/buku', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['judul', 'pengarang', 'stok']);
});

it('rejects negative stok', function () {
    $admin = adminBuku();
    $response = $this->actingAs($admin, 'web')->post('/api/buku', [
        'judul' => 'Test',
        'pengarang' => 'Test',
        'stok' => -1,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['stok']);
});

it('updates a buku', function () {
    $buku = Buku::create(['judul' => 'Old', 'pengarang' => 'X', 'stok' => 1]);
    $admin = adminBuku();

    $response = $this->actingAs($admin, 'web')->put("/api/buku/{$buku->id_buku}", [
        'judul' => 'New',
        'pengarang' => 'X',
        'stok' => 5,
    ]);

    $response->assertOk();
    $response->assertJson(['judul' => 'New', 'stok' => 5]);
});

it('deletes a buku', function () {
    $buku = Buku::create(['judul' => 'ToDelete', 'pengarang' => 'X', 'stok' => 1]);
    $admin = adminBuku();

    $response = $this->actingAs($admin, 'web')->delete("/api/buku/{$buku->id_buku}");

    $response->assertNoContent();
    $this->assertSoftDeleted('buku', ['id_buku' => $buku->id_buku]);
});

it('allows anggota to list buku', function () {
    Buku::create(['judul' => 'Laut Bercerita', 'pengarang' => 'Leila', 'stok' => 3]);
    Buku::create(['judul' => 'Bumi Manusia', 'pengarang' => 'Pram', 'stok' => 0]);

    $anggota = anggotaBuku();
    $response = $this->actingAs($anggota, 'web')->get('/api/buku');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

it('allows anggota to search buku by q', function () {
    Buku::create(['judul' => 'Laut Bercerita', 'pengarang' => 'Leila', 'stok' => 3]);
    Buku::create(['judul' => 'Bumi Manusia', 'pengarang' => 'Pram', 'stok' => 0]);

    $anggota = anggotaBuku();
    $response = $this->actingAs($anggota, 'web')->get('/api/buku?q=Laut');

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['judul' => 'Laut Bercerita']);
});

it('allows anggota to show a single buku', function () {
    $buku = Buku::create(['judul' => 'Laut Bercerita', 'pengarang' => 'Leila', 'stok' => 3]);

    $anggota = anggotaBuku();
    $response = $this->actingAs($anggota, 'web')->get("/api/buku/{$buku->id_buku}");

    $response->assertOk();
    $response->assertJsonFragment(['judul' => 'Laut Bercerita']);
});

it('rejects anggota from creating buku', function () {
    $anggota = anggotaBuku();
    $response = $this->actingAs($anggota, 'web')->post('/api/buku', [
        'judul' => 'Laut Bercerita',
        'pengarang' => 'Leila S. Chudori',
        'stok' => 3,
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['message' => 'Akses ditolak. Anda tidak memiliki hak untuk tindakan ini.']);
});

it('rejects anggota from updating buku', function () {
    $buku = Buku::create(['judul' => 'Old', 'pengarang' => 'X', 'stok' => 1]);

    $anggota = anggotaBuku();
    $response = $this->actingAs($anggota, 'web')->put("/api/buku/{$buku->id_buku}", [
        'judul' => 'New',
        'pengarang' => 'X',
        'stok' => 5,
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['message' => 'Akses ditolak. Anda tidak memiliki hak untuk tindakan ini.']);
});

it('rejects anggota from deleting buku', function () {
    $buku = Buku::create(['judul' => 'ToDelete', 'pengarang' => 'X', 'stok' => 1]);

    $anggota = anggotaBuku();
    $response = $this->actingAs($anggota, 'web')->delete("/api/buku/{$buku->id_buku}");

    $response->assertStatus(403);
    $response->assertJsonFragment(['message' => 'Akses ditolak. Anda tidak memiliki hak untuk tindakan ini.']);
});

it('still requires auth on GET /api/buku', function () {
    $response = $this->getJson('/api/buku');

    $response->assertStatus(401);
});
