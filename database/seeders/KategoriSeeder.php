<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Fiksi',
            'Non-Fiksi',
            'Sains & Teknologi',
            'Sejarah',
            'Biografi',
            'Bisnis & Ekonomi',
            'Anak-anak',
            'Kesehatan',
        ];

        foreach ($categories as $nama) {
            Kategori::updateOrCreate(['nama_kategori' => $nama], ['nama_kategori' => $nama]);
        }
    }
}
