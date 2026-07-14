<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    public function run(): void
    {
        $anggotaByName = Anggota::pluck('id_anggota', 'nama')->toArray();
        $bukuByJudul = Buku::pluck('id_buku', 'judul')->toArray();

        $loans = [
            [101, 'Nadia Putri Ramadhani', '2026-06-20', '2026-06-27', 'dipinjam', [['judul' => 'Laut Bercerita', 'tgl_kembali' => null, 'denda' => null]]],
            [102, 'Nadia Putri Ramadhani', '2026-05-10', '2026-05-17', 'selesai', [['judul' => 'Atomic Habits', 'tgl_kembali' => '2026-05-22', 'denda' => ['jumlah_hari' => 5, 'jumlah_denda' => 5000, 'status_bayar' => 'belum', 'tgl_bayar' => null]]]],
            [103, 'Nadia Putri Ramadhani', '2026-04-01', '2026-04-08', 'selesai', [['judul' => 'Berpikir Cepat dan Lambat', 'tgl_kembali' => '2026-04-08', 'denda' => null]]],
            [104, 'Rizky Ahmad Fauzan', '2026-06-15', '2026-06-22', 'dipinjam', [['judul' => 'Sapiens: Riwayat Singkat Umat Manusia', 'tgl_kembali' => null, 'denda' => null], ['judul' => 'Rich Dad Poor Dad', 'tgl_kembali' => null, 'denda' => null]]],
            [105, 'Sari Wulandari', '2026-06-01', '2026-06-08', 'selesai', [['judul' => 'Bumi Manusia', 'tgl_kembali' => '2026-06-15', 'denda' => ['jumlah_hari' => 7, 'jumlah_denda' => 7000, 'status_bayar' => 'lunas', 'tgl_bayar' => '2026-06-16']]]],
            [106, 'Bagus Prasetyo', '2026-06-25', '2026-07-02', 'dipinjam', [['judul' => 'Cosmos', 'tgl_kembali' => null, 'denda' => null]]],
            [107, 'Dewi Anggraini', '2026-05-20', '2026-05-27', 'selesai', [['judul' => 'Habibie & Ainun', 'tgl_kembali' => '2026-06-02', 'denda' => ['jumlah_hari' => 6, 'jumlah_denda' => 6000, 'status_bayar' => 'belum', 'tgl_bayar' => null]]]],
            [108, 'Fajar Nugroho', '2026-07-01', '2026-07-08', 'dipinjam', [['judul' => 'Clean Code', 'tgl_kembali' => null, 'denda' => null]]],
            [109, 'Melati Kusuma', '2026-06-10', '2026-06-17', 'selesai', [['judul' => 'Negeri 5 Menara', 'tgl_kembali' => '2026-06-17', 'denda' => null]]],
            [110, 'Andra Wibisono', '2026-06-28', '2026-07-05', 'dipinjam', [['judul' => 'Steve Jobs', 'tgl_kembali' => null, 'denda' => null]]],
            [111, 'Citra Puspita', '2026-05-01', '2026-05-08', 'selesai', [['judul' => 'Panduan Hidup Sehat', 'tgl_kembali' => '2026-05-20', 'denda' => ['jumlah_hari' => 12, 'jumlah_denda' => 12000, 'status_bayar' => 'lunas', 'tgl_bayar' => '2026-05-21']]]],
            [112, 'Doni Setiawan', '2026-07-02', '2026-07-09', 'dipinjam', [['judul' => 'Introduction to Algorithms', 'tgl_kembali' => null, 'denda' => null]]],
            [113, 'Intan Permatasari', '2026-06-05', '2026-06-12', 'selesai', [['judul' => 'Sejarah Dunia yang Disederhanakan', 'tgl_kembali' => '2026-06-14', 'denda' => ['jumlah_hari' => 2, 'jumlah_denda' => 2000, 'status_bayar' => 'belum', 'tgl_bayar' => null]]]],
            [114, 'Yusuf Maulana', '2026-07-05', '2026-07-12', 'dipinjam', [['judul' => 'Filosofi Teras', 'tgl_kembali' => null, 'denda' => null]]],
            [115, 'Putri Amelia', '2026-06-18', '2026-06-25', 'selesai', [['judul' => 'Sejarah Indonesia Modern', 'tgl_kembali' => '2026-06-25', 'denda' => null]]],
            [116, 'Galih Saputra', '2026-07-08', '2026-07-15', 'dipinjam', [['judul' => 'Si Kancil dan Buaya', 'tgl_kembali' => null, 'denda' => null]]],
            [117, 'Wahyu Hidayat', '2026-05-25', '2026-06-01', 'selesai', [['judul' => 'Petualangan Si Kucing', 'tgl_kembali' => '2026-06-06', 'denda' => ['jumlah_hari' => 5, 'jumlah_denda' => 5000, 'status_bayar' => 'lunas', 'tgl_bayar' => '2026-06-07']]]],
            [118, 'Ratna Sari Dewi', '2026-07-10', '2026-07-17', 'dipinjam', [['judul' => 'Laut Bercerita', 'tgl_kembali' => null, 'denda' => null]]],
        ];

        foreach ($loans as [$id, $anggotaName, $tglPinjam, $tglJatuhTempo, $status, $items]) {
            $peminjaman = Peminjaman::updateOrCreate(
                ['id_peminjaman' => $id],
                [
                    'id_anggota' => $anggotaByName[$anggotaName] ?? null,
                    'tgl_pinjam' => $tglPinjam,
                    'tgl_jatuh_tempo' => $tglJatuhTempo,
                    'status' => $status,
                ]
            );

            foreach ($items as $item) {
                $detail = DetailPeminjaman::updateOrCreate(
                    [
                        'id_peminjaman' => $peminjaman->id_peminjaman,
                        'id_buku' => $bukuByJudul[$item['judul']] ?? null,
                    ],
                    [
                        'tgl_kembali' => $item['tgl_kembali'],
                    ]
                );

                if ($item['denda'] !== null) {
                    Denda::updateOrCreate(
                        ['id_detail' => $detail->id_detail],
                        $item['denda']
                    );
                }
            }
        }
    }
}
