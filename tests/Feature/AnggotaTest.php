<?php

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function adminAnggota(): User
{
    return User::create([
        'username' => 'admin1',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);
}

it('lists all anggota', function () {
    $admin = adminAnggota();
    $u = User::create(['username' => 'nadia.putri', 'password' => Hash::make('anggota123'), 'role' => 'anggota']);
    Anggota::create(['id_user' => $u->id_user, 'nama' => 'Nadia', 'tgl_daftar' => '2023-02-10']);

    $response = $this->actingAs($admin, 'web')->get('/api/anggota');
    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['nama' => 'Nadia']);
});

it('creates anggota with user', function () {
    $admin = adminAnggota();
    $response = $this->actingAs($admin, 'web')->post('/api/anggota', [
        'nama' => 'Nadia Putri',
        'alamat' => 'Jl. Kenanga 12',
        'email' => 'nadia@mail.com',
        'tgl_daftar' => '2023-02-10',
        'username' => 'nadia.putri',
        'password' => 'anggota123',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('user', ['username' => 'nadia.putri', 'role' => 'anggota']);
    $this->assertDatabaseHas('anggota', ['nama' => 'Nadia Putri']);
});

it('rejects duplicate username', function () {
    $admin = adminAnggota();
    User::create(['username' => 'nadia.putri', 'password' => Hash::make('x'), 'role' => 'anggota']);

    $response = $this->actingAs($admin, 'web')->post('/api/anggota', [
        'nama' => 'X',
        'tgl_daftar' => '2023-01-01',
        'username' => 'nadia.putri',
        'password' => 'secret123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['username']);
});

it('updates anggota and password', function () {
    $admin = adminAnggota();
    $u = User::create(['username' => 'nadia', 'password' => Hash::make('oldpass'), 'role' => 'anggota']);
    $a = Anggota::create(['id_user' => $u->id_user, 'nama' => 'Old Name', 'tgl_daftar' => '2023-01-01']);

    $response = $this->actingAs($admin, 'web')->put("/api/anggota/{$a->id_anggota}", [
        'nama' => 'New Name',
        'password' => 'newpassword',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('anggota', ['id_anggota' => $a->id_anggota, 'nama' => 'New Name']);
    expect(Hash::check('newpassword', $u->fresh()->password))->toBeTrue();
});

it('deletes anggota and user', function () {
    $admin = adminAnggota();
    $u = User::create(['username' => 'nadia', 'password' => Hash::make('x'), 'role' => 'anggota']);
    $a = Anggota::create(['id_user' => $u->id_user, 'nama' => 'Nadia', 'tgl_daftar' => '2023-01-01']);

    $response = $this->actingAs($admin, 'web')->delete("/api/anggota/{$a->id_anggota}");
    $response->assertNoContent();
    $this->assertSoftDeleted('anggota', ['id_anggota' => $a->id_anggota]);
    $this->assertDatabaseMissing('user', ['id_user' => $u->id_user]);
});
