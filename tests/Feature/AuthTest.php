<?php

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate', ['--seed' => false]);
});

function seedAdminAndAnggota(): array
{
    $admin = User::create([
        'username' => 'admin1',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);
    $anggotaUser = User::create([
        'username' => 'nadia.putri',
        'password' => Hash::make('anggota123'),
        'role' => 'anggota',
    ]);
    $anggota = Anggota::create([
        'id_user' => $anggotaUser->id_user,
        'nama' => 'Nadia Putri Ramadhani',
        'alamat' => 'Jl. Kenanga No. 12, Bandung',
        'no_telp' => '0812-3456-7801',
        'email' => 'nadia.putri@mail.com',
        'tgl_daftar' => '2023-02-10',
    ]);
    return ['admin' => $admin, 'anggota' => $anggota, 'anggotaUser' => $anggotaUser];
}

it('allows admin to login with correct credentials', function () {
    ['admin' => $admin] = seedAdminAndAnggota();

    $response = $this->post('/api/login', [
        'username' => 'admin1',
        'password' => 'admin123',
    ]);

    $response->assertOk();
    $response->assertJson([
        'user' => [
            'username' => 'admin1',
            'role' => 'admin',
        ],
        'anggota' => null,
    ]);
});

it('allows anggota to login and returns anggota record', function () {
    seedAdminAndAnggota();

    $response = $this->post('/api/login', [
        'username' => 'nadia.putri',
        'password' => 'anggota123',
    ]);

    $response->assertOk();
    $response->assertJson([
        'user' => [
            'username' => 'nadia.putri',
            'role' => 'anggota',
        ],
    ]);
    $response->assertJsonStructure(['user' => ['id_user', 'username', 'role'], 'anggota' => ['id_anggota', 'nama']]);
});

it('rejects login with wrong password', function () {
    seedAdminAndAnggota();

    $response = $this->post('/api/login', [
        'username' => 'admin1',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['username']);
});

it('rejects login with missing fields', function () {
    $response = $this->post('/api/login', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['username', 'password']);
});

it('returns me for authenticated admin', function () {
    ['admin' => $admin] = seedAdminAndAnggota();

    $response = $this->actingAs($admin, 'web')->get('/api/me');

    $response->assertOk();
    $response->assertJson([
        'user' => [
            'username' => 'admin1',
            'role' => 'admin',
        ],
        'anggota' => null,
    ]);
});

it('returns me for authenticated anggota with anggota record', function () {
    ['anggotaUser' => $anggotaUser] = seedAdminAndAnggota();

    $response = $this->actingAs($anggotaUser, 'web')->get('/api/me');

    $response->assertOk();
    $response->assertJson([
        'user' => [
            'username' => 'nadia.putri',
            'role' => 'anggota',
        ],
    ]);
    $response->assertJsonStructure(['anggota' => ['id_anggota', 'nama', 'email']]);
});

it('returns 401 for me when not authenticated', function () {
    $response = $this->get('/api/me');

    $response->assertStatus(401);
});

it('logs out authenticated user', function () {
    ['admin' => $admin] = seedAdminAndAnggota();

    $response = $this->actingAs($admin, 'web')->post('/api/logout');

    $response->assertOk();
    $response->assertJson(['message' => 'Logout berhasil.']);
});
