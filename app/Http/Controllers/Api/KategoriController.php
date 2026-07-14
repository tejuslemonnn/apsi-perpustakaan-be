<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Http\Resources\KategoriResource;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        return KategoriResource::collection(Kategori::orderBy('nama_kategori')->get());
    }

    public function store(StoreKategoriRequest $request)
    {
        $kategori = Kategori::create($request->validated());
        return new KategoriResource($kategori);
    }

    public function show(Kategori $kategori)
    {
        return new KategoriResource($kategori);
    }

    public function update(UpdateKategoriRequest $request, Kategori $kategori)
    {
        $kategori->update($request->validated());
        return new KategoriResource($kategori);
    }

    public function destroy(Kategori $kategori)
    {
        $kategori->delete();
        return response()->noContent();
    }
}
