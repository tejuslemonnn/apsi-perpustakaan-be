<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DendaResource;
use App\Models\Anggota;
use App\Models\Denda;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DendaController extends Controller
{
    /**
     * Member: list my fines.
     */
    public function myFines(Request $request)
    {
        $user = $request->user();
        $anggota = $user ? $user->anggota : null;
        if (! $anggota) {
            return response()->json(['data' => []]);
        }

        $fines = Denda::with(['detailPeminjaman.peminjaman.anggota', 'detailPeminjaman.buku'])
            ->whereHas('detailPeminjaman.peminjaman', function ($q) use ($anggota) {
                $q->where('id_anggota', $anggota->id_anggota);
            })
            ->orderBy('id_denda', 'desc')
            ->get();

        return DendaResource::collection($fines);
    }

    /**
     * Admin: list all fines.
     */
    public function adminFines(Request $request)
    {
        $fines = Denda::with(['detailPeminjaman.peminjaman.anggota', 'detailPeminjaman.buku'])
            ->orderBy('id_denda', 'desc')
            ->get();

        return DendaResource::collection($fines);
    }

    /**
     * Member: confirm payment (status -> menunggu_verifikasi).
     */
    public function confirmPay(Denda $denda): JsonResponse
    {
        $user = auth()->user();
        $anggota = $user ? $user->anggota : null;
        // Check ownership: this fine must belong to a loan of current user's anggota
        $denda->load('detailPeminjaman.peminjaman');
        if (! $anggota || $denda->detailPeminjaman->peminjaman->id_anggota !== $anggota->id_anggota) {
            return response()->json(['message' => 'Denda tidak ditemukan.'], 404);
        }

        if ($denda->status_bayar !== 'belum') {
            return response()->json(['message' => 'Denda ini tidak dapat dikonfirmasi pembayarannya.'], 422);
        }

        $denda->update(['status_bayar' => 'menunggu_verifikasi']);

        return response()->json([
            'message' => 'Konfirmasi pembayaran berhasil. Silakan tunggu verifikasi admin.',
            'denda' => new DendaResource($denda->fresh()->load('detailPeminjaman.peminjaman.anggota', 'detailPeminjaman.buku')),
        ]);
    }

    /**
     * Admin: verify payment (status -> lunas, set tgl_bayar).
     */
    public function verifyPay(Denda $denda): JsonResponse
    {
        if ($denda->status_bayar === 'lunas') {
            return response()->json(['message' => 'Denda ini sudah lunas.'], 422);
        }

        $denda->update([
            'status_bayar' => 'lunas',
            'tgl_bayar' => Carbon::today(),
        ]);

        return response()->json([
            'message' => 'Denda berhasil diverifikasi lunas.',
            'denda' => new DendaResource($denda->fresh()->load('detailPeminjaman.peminjaman.anggota', 'detailPeminjaman.buku')),
        ]);
    }
}
