<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBukuRequest;
use App\Http\Requests\UpdateBukuRequest;
use App\Http\Resources\BukuResource;
use App\Models\Buku;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        $query = Buku::with('kategori');

        if ($q = $request->query('q')) {
            $query->where(function ($q2) use ($q) {
                $q2->where('judul', 'like', "%{$q}%")
                    ->orWhere('pengarang', 'like', "%{$q}%")
                    ->orWhere('isbn', 'like', "%{$q}%");
            });
        }

        if ($sort = $request->query('sort')) {
            $direction = $request->query('direction', 'asc');
            $allowed = ['judul', 'pengarang', 'tahun_terbit', 'stok'];
            if (in_array($sort, $allowed, true)) {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->orderBy('judul');
        }

        return BukuResource::collection($query->get());
    }

    public function store(StoreBukuRequest $request)
    {
        $buku = Buku::create($request->validated());
        $buku->load('kategori');
        return new BukuResource($buku);
    }

    public function show(Buku $buku)
    {
        $buku->load('kategori');
        return new BukuResource($buku);
    }

    public function update(UpdateBukuRequest $request, Buku $buku)
    {
        $buku->update($request->validated());
        $buku->load('kategori');
        return new BukuResource($buku);
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();
        return response()->noContent();
    }
}
