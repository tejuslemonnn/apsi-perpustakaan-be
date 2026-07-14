<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanResource;
use App\Models\Denda;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    public function adminLoans(Request $request)
    {
        $query = Peminjaman::with(['anggota', 'detailPeminjaman.buku', 'detailPeminjaman.denda']);

        if ($q = $request->query('q')) {
            $query->whereHas('anggota', function ($q2) use ($q) {
                $q2->where('nama', 'like', "%{$q}%");
            });
        }

        $loans = $query->orderBy('tgl_pinjam', 'desc')->get();

        return PeminjamanResource::collection($loans);
    }

    public function processReturn(Peminjaman $peminjaman): JsonResponse
    {
        if ($peminjaman->status === 'selesai') {
            return response()->json(['message' => 'Peminjaman ini sudah selesai.'], 422);
        }

        $today = Carbon::today();
        $due = Carbon::parse($peminjaman->tgl_jatuh_tempo);

        if ($today->gt($due)) {
            $lateDays = (int) floor($due->diffInDays($today));
        } else {
            $lateDays = 0;
        }

        $finePerDay = (int) config('app.fine_per_day', 1000);
        $fineAmount = $lateDays * $finePerDay;

        $result = DB::transaction(function () use ($peminjaman, $today, $lateDays, $fineAmount) {
            foreach ($peminjaman->detailPeminjaman as $detail) {
                if (is_null($detail->tgl_kembali)) {
                    $detail->update(['tgl_kembali' => $today]);
                }
            }

            if ($lateDays > 0) {
                foreach ($peminjaman->detailPeminjaman as $detail) {
                    if ($detail->denda) {
                        continue;
                    }
                    Denda::create([
                        'id_detail' => $detail->id_detail,
                        'jumlah_hari' => $lateDays,
                        'jumlah_denda' => $fineAmount,
                        'status_bayar' => 'belum',
                    ]);
                }
            }

            foreach ($peminjaman->detailPeminjaman as $detail) {
                if ($detail->buku) {
                    $detail->buku->increment('stok');
                }
            }

            $peminjaman->update(['status' => 'selesai']);

            return $peminjaman->fresh();
        });

        $result->load(['anggota', 'detailPeminjaman.buku', 'detailPeminjaman.denda']);

        return response()->json([
            'message' => 'Pengembalian berhasil diproses.',
            'peminjaman' => new PeminjamanResource($result),
            'lateDays' => $lateDays,
            'fineAmount' => $fineAmount,
        ]);
    }
}
