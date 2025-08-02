<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use App\Models\Kategori;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $produks = Produk::join('kategoris', 'kategoris.id', 'produks.kategori_id')
            ->orderBy('produks.id')
            ->select('produks.*', 'nama_kategori')
            ->when($search, function ($q, $search) {
                return $q->where('kode_produk', 'like', "%{$search}%")
                         ->orWhere('nama_produk', 'like', "%{$search}%");
            })
            ->paginate();

        return view('produk.index', ['produks' => $produks]);
    }

    public function create()
    {
        $dataKategori = Kategori::orderBy('nama_kategori')->get();

        $kategoris = [ 
            ['', 'Pilih Kategori'] 
        ];
        
        foreach ($dataKategori as $kategori) {
            $kategoris[] = [$kategori->id, $kategori->nama_kategori];
        }

        return view('produk.create', ['kategoris' => $kategoris]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => ['required', 'max:250', 'unique:produks'],
            'nama_produk' => ['required', 'max:150'],
            'harga' => ['required', 'numeric'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
        ]);

        Produk::create([
        'kode_produk' => $request->kode_produk,
        'nama_produk' => $request->nama_produk,
        'harga' => $request->harga,
        'kategori_id' => $request->kategori_id,
        ]);

        return redirect()->route('produk.index')->with('store', 'success');
    }

    public function show(Produk $produk)
    {
        abort(404);
    }

    public function edit(Produk $produk)
    {
        $dataKategori = Kategori::orderBy('nama_kategori')->get();

        $kategoris = [ 
            ['', 'Pilih Kategori'] 
        ];
        
        foreach ($dataKategori as $kategori) {
            $kategoris[] = [$kategori->id, $kategori->nama_kategori];
        }

        return view('produk.edit', [
            'produk' => $produk,
            'kategoris' => $kategoris,
        ]);
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'kode_produk' => ['required', 'max:250', 'unique:produks,kode_produk,' . $produk->id],
            'nama_produk' => ['required', 'max:150'],
            'harga' => ['required', 'numeric'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
        ]);

        $produk->update([
        'kode_produk' => $request->kode_produk,
        'nama_produk' => $request->nama_produk,
        'harga' => $request->harga,
        'kategori_id' => $request->kategori_id,
        ]);

        return redirect()->route('produk.index')->with('update', 'success');
    }

    public function destroy(Produk $produk)
    {
        $produk->delete();

        return back()->with('destroy', 'success');
    }
}
