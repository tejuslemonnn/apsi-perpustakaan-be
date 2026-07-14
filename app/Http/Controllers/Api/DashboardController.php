<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanResource;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\Peminjaman;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBuku = Buku::count();
        $totalAnggota = Anggota::count();
        $peminjamanAktif = Peminjaman::where('status', 'dipinjam')
            ->orWhere('status', 'menunggu_verifikasi')
            ->count();
        $dendaBelumLunas = Denda::whereIn('status_bayar', ['belum', 'menunggu_verifikasi'])->count();

        $recentLoans = Peminjaman::with(['anggota', 'detailPeminjaman.buku'])
            ->orderBy('tgl_pinjam', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'totalBuku' => $totalBuku,
            'totalAnggota' => $totalAnggota,
            'peminjamanAktif' => $peminjamanAktif,
            'dendaBelumLunas' => $dendaBelumLunas,
            'recentLoans' => PeminjamanResource::collection($recentLoans),
        ]);
    }
}
