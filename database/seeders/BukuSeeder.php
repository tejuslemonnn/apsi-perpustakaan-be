<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriMap = Kategori::pluck('id_kategori', 'nama_kategori')->toArray();

        $books = [
            ['isbn' => '978-602-424-101-1', 'judul' => 'Laut Bercerita', 'pengarang' => 'Leila S. Chudori', 'penerbit' => 'KPG', 'tahun' => 2017, 'kategori' => 'Fiksi', 'stok' => 3],
            ['isbn' => '978-979-97312-3-4', 'judul' => 'Bumi Manusia', 'pengarang' => 'Pramoedya Ananta Toer', 'penerbit' => 'Lentera Dipantara', 'tahun' => 1980, 'kategori' => 'Fiksi', 'stok' => 0],
            ['isbn' => '978-602-9193-70-3', 'judul' => 'Sapiens: Riwayat Singkat Umat Manusia', 'pengarang' => 'Yuval Noah Harari', 'penerbit' => 'Pustaka Alvabet', 'tahun' => 2017, 'kategori' => 'Non-Fiksi', 'stok' => 5],
            ['isbn' => '978-602-06-2482-8', 'judul' => 'Atomic Habits', 'pengarang' => 'James Clear', 'penerbit' => 'Gramedia Pustaka Utama', 'tahun' => 2019, 'kategori' => 'Bisnis & Ekonomi', 'stok' => 4],
            ['isbn' => '978-602-291-235-3', 'judul' => 'Filosofi Teras', 'pengarang' => 'Henry Manampiring', 'penerbit' => 'Kompas', 'tahun' => 2018, 'kategori' => 'Non-Fiksi', 'stok' => 2],
            ['isbn' => '978-0-345-33135-9', 'judul' => 'Cosmos', 'pengarang' => 'Carl Sagan', 'penerbit' => 'Pustaka Sains', 'tahun' => 1980, 'kategori' => 'Sains & Teknologi', 'stok' => 0],
            ['isbn' => '978-979-024-712-7', 'judul' => 'Sejarah Indonesia Modern', 'pengarang' => 'M.C. Ricklefs', 'penerbit' => 'Serambi', 'tahun' => 2008, 'kategori' => 'Sejarah', 'stok' => 6],
            ['isbn' => '978-979-3695-31-6', 'judul' => 'Habibie & Ainun', 'pengarang' => 'B.J. Habibie', 'penerbit' => 'THC Mandiri', 'tahun' => 2010, 'kategori' => 'Biografi', 'stok' => 1],
            ['isbn' => '978-602-03-3491-1', 'judul' => 'Rich Dad Poor Dad', 'pengarang' => 'Robert T. Kiyosaki', 'penerbit' => 'Gramedia Pustaka Utama', 'tahun' => 1997, 'kategori' => 'Bisnis & Ekonomi', 'stok' => 3],
            ['isbn' => '978-602-496-012-2', 'judul' => 'Si Kancil dan Buaya', 'pengarang' => 'Anonim', 'penerbit' => 'Elex Media', 'tahun' => 2015, 'kategori' => 'Anak-anak', 'stok' => 8],
            ['isbn' => '978-0-13-235088-4', 'judul' => 'Clean Code', 'pengarang' => 'Robert C. Martin', 'penerbit' => 'Prentice Hall', 'tahun' => 2008, 'kategori' => 'Sains & Teknologi', 'stok' => 2],
            ['isbn' => '978-979-22-4861-6', 'judul' => 'Negeri 5 Menara', 'pengarang' => 'Ahmad Fuadi', 'penerbit' => 'Gramedia Pustaka Utama', 'tahun' => 2009, 'kategori' => 'Fiksi', 'stok' => 0],
            ['isbn' => '978-602-291-004-5', 'judul' => 'Sejarah Dunia yang Disederhanakan', 'pengarang' => 'Ernst Gombrich', 'penerbit' => 'Bentang Pustaka', 'tahun' => 2013, 'kategori' => 'Sejarah', 'stok' => 4],
            ['isbn' => '978-602-03-0034-3', 'judul' => 'Steve Jobs', 'pengarang' => 'Walter Isaacson', 'penerbit' => 'Gramedia Pustaka Utama', 'tahun' => 2011, 'kategori' => 'Biografi', 'stok' => 2],
            ['isbn' => '978-602-03-0781-6', 'judul' => 'Berpikir Cepat dan Lambat', 'pengarang' => 'Daniel Kahneman', 'penerbit' => 'Gramedia Pustaka Utama', 'tahun' => 2011, 'kategori' => 'Non-Fiksi', 'stok' => 3],
            ['isbn' => '978-602-412-345-6', 'judul' => 'Panduan Hidup Sehat', 'pengarang' => 'dr. Andi Wijaya', 'penerbit' => 'Kompas', 'tahun' => 2020, 'kategori' => 'Kesehatan', 'stok' => 5],
            ['isbn' => '978-602-496-078-8', 'judul' => 'Petualangan Si Kucing', 'pengarang' => 'Dian Kristiani', 'penerbit' => 'Elex Media', 'tahun' => 2018, 'kategori' => 'Anak-anak', 'stok' => 0],
            ['isbn' => '978-0-262-03384-8', 'judul' => 'Introduction to Algorithms', 'pengarang' => 'Thomas H. Cormen', 'penerbit' => 'MIT Press', 'tahun' => 2009, 'kategori' => 'Sains & Teknologi', 'stok' => 1],
        ];

        foreach ($books as $b) {
            Buku::updateOrCreate(
                ['isbn' => $b['isbn']],
                [
                    'judul' => $b['judul'],
                    'pengarang' => $b['pengarang'],
                    'penerbit' => $b['penerbit'],
                    'tahun_terbit' => $b['tahun'],
                    'id_kategori' => $kategoriMap[$b['kategori']] ?? null,
                    'stok' => $b['stok'],
                ]
            );
        }
    }
}
