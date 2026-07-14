<?php

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function makeAdmin(): User
{
    return User::create([
        'username' => 'admin1',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);
}

function makeAnggota(): User
{
    return User::create([
        'username' => 'nadia.putri',
        'password' => Hash::make('anggota123'),
        'role' => 'anggota',
    ]);
}

it('lists all kategori', function () {
    Kategori::create(['nama_kategori' => 'Fiksi']);
    Kategori::create(['nama_kategori' => 'Sains']);

    $admin = makeAdmin();
    $response = $this->actingAs($admin, 'web')->get('/api/kategori');

    $response->assertOk();
    $response->assertJsonCount(2);
});

it('creates a kategori', function () {
    $admin = makeAdmin();
    $response = $this->actingAs($admin, 'web')->post('/api/kategori', [
        'nama_kategori' => 'Fiksi',
    ]);

    $response->assertCreated();
    $response->assertJson(['nama_kategori' => 'Fiksi']);
    $this->assertDatabaseHas('kategori', ['nama_kategori' => 'Fiksi']);
});

it('rejects duplicate kategori name', function () {
    Kategori::create(['nama_kategori' => 'Fiksi']);
    $admin = makeAdmin();

    $response = $this->actingAs($admin, 'web')->post('/api/kategori', [
        'nama_kategori' => 'Fiksi',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['nama_kategori']);
});

it('updates a kategori', function () {
    $kategori = Kategori::create(['nama_kategori' => 'Fiksi']);
    $admin = makeAdmin();

    $response = $this->actingAs($admin, 'web')->put("/api/kategori/{$kategori->id_kategori}", [
        'nama_kategori' => 'Fiksi Updated',
    ]);

    $response->assertOk();
    $response->assertJson(['nama_kategori' => 'Fiksi Updated']);
});

it('deletes a kategori', function () {
    $kategori = Kategori::create(['nama_kategori' => 'Fiksi']);
    $admin = makeAdmin();

    $response = $this->actingAs($admin, 'web')->delete("/api/kategori/{$kategori->id_kategori}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('kategori', ['id_kategori' => $kategori->id_kategori]);
});

it('allows anggota to list kategori', function () {
    Kategori::create(['nama_kategori' => 'Fiksi']);
    Kategori::create(['nama_kategori' => 'Sains']);

    $anggota = makeAnggota();
    $response = $this->actingAs($anggota, 'web')->get('/api/kategori');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

it('allows anggota to show a single kategori', function () {
    $kategori = Kategori::create(['nama_kategori' => 'Fiksi']);

    $anggota = makeAnggota();
    $response = $this->actingAs($anggota, 'web')->get("/api/kategori/{$kategori->id_kategori}");

    $response->assertOk();
    $response->assertJsonFragment(['nama_kategori' => 'Fiksi']);
});

it('rejects anggota from creating kategori', function () {
    $anggota = makeAnggota();
    $response = $this->actingAs($anggota, 'web')->post('/api/kategori', [
        'nama_kategori' => 'Teknologi',
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['message' => 'Akses ditolak. Anda tidak memiliki hak untuk tindakan ini.']);
});

it('rejects anggota from updating kategori', function () {
    $kategori = Kategori::create(['nama_kategori' => 'Fiksi']);

    $anggota = makeAnggota();
    $response = $this->actingAs($anggota, 'web')->put("/api/kategori/{$kategori->id_kategori}", [
        'nama_kategori' => 'Fiksi Updated',
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['message' => 'Akses ditolak. Anda tidak memiliki hak untuk tindakan ini.']);
});

it('rejects anggota from deleting kategori', function () {
    $kategori = Kategori::create(['nama_kategori' => 'Fiksi']);

    $anggota = makeAnggota();
    $response = $this->actingAs($anggota, 'web')->delete("/api/kategori/{$kategori->id_kategori}");

    $response->assertStatus(403);
    $response->assertJsonFragment(['message' => 'Akses ditolak. Anda tidak memiliki hak untuk tindakan ini.']);
});
