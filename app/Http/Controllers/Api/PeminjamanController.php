<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeminjamanRequest;
use App\Http\Resources\PeminjamanResource;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanController extends Controller
{
    public function store(StorePeminjamanRequest $request)
    {
        $data = $request->validated();
        $anggota = $this->currentAnggota();
        if (! $anggota) {
            return response()->json(['message' => 'Hanya anggota yang dapat meminjam.'], 403);
        }

        $buku = Buku::find($data['id_buku']);
        if (! $buku) {
            throw ValidationException::withMessages(['id_buku' => ['Buku tidak ditemukan.']]);
        }
        if ($buku->stok <= 0) {
            throw ValidationException::withMessages(['id_buku' => ['Stok buku habis.']]);
        }

        $peminjaman = DB::transaction(function () use ($data, $anggota, $buku) {
            $tglPinjam = Carbon::today();
            $loanDays = (int) config('app.loan_period_days', 7);
            $tglJatuhTempo = $tglPinjam->copy()->addDays($loanDays);

            $p = Peminjaman::create([
                'id_anggota' => $anggota->id_anggota,
                'tgl_pinjam' => $tglPinjam,
                'tgl_jatuh_tempo' => $tglJatuhTempo,
                'status' => 'dipinjam',
            ]);

            DetailPeminjaman::create([
                'id_peminjaman' => $p->id_peminjaman,
                'id_buku' => $buku->id_buku,
            ]);

            $buku->decrement('stok');

            return $p;
        });

        $peminjaman->load(['anggota', 'detailPeminjaman.buku']);
        return new PeminjamanResource($peminjaman);
    }

    public function requestReturn(Peminjaman $peminjaman): JsonResponse
    {
        $anggota = $this->currentAnggota();
        if (! $anggota || $peminjaman->id_anggota !== $anggota->id_anggota) {
            return response()->json(['message' => 'Peminjaman tidak ditemukan.'], 404);
        }

        if ($peminjaman->status !== 'dipinjam') {
            throw ValidationException::withMessages([
                'status' => ['Hanya peminjaman berstatus dipinjam yang dapat diajukan pengembalian.'],
            ]);
        }

        $peminjaman->update(['status' => 'menunggu_verifikasi']);

        return response()->json([
            'message' => 'Pengembalian berhasil diajukan. Silakan tunggu verifikasi admin.',
            'peminjaman' => new PeminjamanResource($peminjaman->load(['anggota', 'detailPeminjaman.buku'])),
        ]);
    }

    public function myLoans()
    {
        $anggota = $this->currentAnggota();
        if (! $anggota) {
            return response()->json(['data' => []]);
        }

        $loans = Peminjaman::with(['anggota', 'detailPeminjaman.buku'])
            ->where('id_anggota', $anggota->id_anggota)
            ->orderBy('tgl_pinjam', 'desc')
            ->get();

        return PeminjamanResource::collection($loans);
    }

    public function show(Peminjaman $peminjaman)
    {
        $user = auth()->user();
        if ($user->role === 'anggota') {
            $anggota = $user->anggota;
            if (! $anggota || $peminjaman->id_anggota !== $anggota->id_anggota) {
                return response()->json(['message' => 'Peminjaman tidak ditemukan.'], 404);
            }
        }

        $peminjaman->load(['anggota', 'detailPeminjaman.buku']);
        return new PeminjamanResource($peminjaman);
    }

    private function currentAnggota(): ?Anggota
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'anggota') {
            return null;
        }
        return $user->anggota()->first();
    }
}
