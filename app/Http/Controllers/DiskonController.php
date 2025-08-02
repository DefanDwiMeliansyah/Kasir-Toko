<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diskon;
use App\Models\Kategori;
use App\Models\Produk;

class DiskonController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $diskons = Diskon::when($search, function ($q, $search) {
                return $q->where('kode_diskon', 'like', "%{$search}%")
                         ->orWhere('nama_diskon', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate();

        if ($search) $diskons->appends(['search' => $search]);

        return view('diskon.index', [
            'diskons' => $diskons
        ]);
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $produks = Produk::orderBy('nama_produk')->get();

        return view('diskon.create', [
            'kategoris' => $kategoris,
            'produks' => $produks
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_diskon' => ['required', 'string', 'max:50', 'unique:diskons,kode_diskon'],
            'nama_diskon' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'jenis_diskon' => ['required', 'in:persen,nominal'],
            'nilai_diskon' => ['required', 'numeric', 'min:0'],
            'maksimal_diskon' => ['nullable', 'numeric', 'min:0'],
            'minimal_belanja' => ['required', 'numeric', 'min:0'],
            'jenis_kondisi' => ['required', 'in:semua,kategori,produk'],
            'kondisi_ids' => ['nullable', 'array'],
            'kuota' => ['nullable', 'integer', 'min:1'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_berakhir' => ['required', 'date', 'after:tanggal_mulai'],
            'aktif' => ['boolean']
        ], [], [
            'kode_diskon' => 'Kode Diskon',
            'nama_diskon' => 'Nama Diskon',
            'jenis_diskon' => 'Jenis Diskon',
            'nilai_diskon' => 'Nilai Diskon',
            'maksimal_diskon' => 'Maksimal Diskon',
            'minimal_belanja' => 'Minimal Belanja',
            'jenis_kondisi' => 'Jenis Kondisi',
            'kondisi_ids' => 'Kondisi',
            'tanggal_mulai' => 'Tanggal Mulai',
            'tanggal_berakhir' => 'Tanggal Berakhir'
        ]);

        // Validasi kondisi_ids berdasarkan jenis_kondisi
        if ($request->jenis_kondisi !== 'semua' && empty($request->kondisi_ids)) {
            return back()->withErrors(['kondisi_ids' => 'Kondisi harus dipilih untuk jenis kondisi ini.']);
        }

        Diskon::create([
            'kode_diskon' => strtoupper($request->kode_diskon),
            'nama_diskon' => $request->nama_diskon,
            'deskripsi' => $request->deskripsi,
            'jenis_diskon' => $request->jenis_diskon,
            'nilai_diskon' => $request->nilai_diskon,
            'maksimal_diskon' => $request->maksimal_diskon,
            'minimal_belanja' => $request->minimal_belanja ?? 0,
            'jenis_kondisi' => $request->jenis_kondisi,
            'kondisi_ids' => $request->jenis_kondisi === 'semua' ? null : $request->kondisi_ids,
            'kuota' => $request->kuota,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'aktif' => $request->has('aktif')
        ]);

        return redirect()->route('diskon.index')->with('store', 'success');
    }

    public function show(Diskon $diskon)
    {
        return view('diskon.show', compact('diskon'));
    }

    public function edit(Diskon $diskon)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $produks = Produk::orderBy('nama_produk')->get();

        return view('diskon.edit', [
            'diskon' => $diskon,
            'kategoris' => $kategoris,
            'produks' => $produks
        ]);
    }

    public function update(Request $request, Diskon $diskon)
    {
        $request->validate([
            'kode_diskon' => ['required', 'string', 'max:50', 'unique:diskons,kode_diskon,' . $diskon->id],
            'nama_diskon' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'jenis_diskon' => ['required', 'in:persen,nominal'],
            'nilai_diskon' => ['required', 'numeric', 'min:0'],
            'maksimal_diskon' => ['nullable', 'numeric', 'min:0'],
            'minimal_belanja' => ['required', 'numeric', 'min:0'],
            'jenis_kondisi' => ['required', 'in:semua,kategori,produk'],
            'kondisi_ids' => ['nullable', 'array'],
            'kuota' => ['nullable', 'integer', 'min:1'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_berakhir' => ['required', 'date', 'after:tanggal_mulai'],
            'aktif' => ['boolean']
        ], [], [
            'kode_diskon' => 'Kode Diskon',
            'nama_diskon' => 'Nama Diskon',
            'jenis_diskon' => 'Jenis Diskon',
            'nilai_diskon' => 'Nilai Diskon',
            'maksimal_diskon' => 'Maksimal Diskon',
            'minimal_belanja' => 'Minimal Belanja',
            'jenis_kondisi' => 'Jenis Kondisi',
            'kondisi_ids' => 'Kondisi',
            'tanggal_mulai' => 'Tanggal Mulai',
            'tanggal_berakhir' => 'Tanggal Berakhir'
        ]);

        // Validasi kondisi_ids berdasarkan jenis_kondisi
        if ($request->jenis_kondisi !== 'semua' && empty($request->kondisi_ids)) {
            return back()->withErrors(['kondisi_ids' => 'Kondisi harus dipilih untuk jenis kondisi ini.']);
        }

        $diskon->update([
            'kode_diskon' => strtoupper($request->kode_diskon),
            'nama_diskon' => $request->nama_diskon,
            'deskripsi' => $request->deskripsi,
            'jenis_diskon' => $request->jenis_diskon,
            'nilai_diskon' => $request->nilai_diskon,
            'maksimal_diskon' => $request->maksimal_diskon,
            'minimal_belanja' => $request->minimal_belanja ?? 0,
            'jenis_kondisi' => $request->jenis_kondisi,
            'kondisi_ids' => $request->jenis_kondisi === 'semua' ? null : $request->kondisi_ids,
            'kuota' => $request->kuota,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'aktif' => $request->has('aktif')
        ]);

        return redirect()->route('diskon.index')->with('update', 'success');
    }

    public function destroy(Diskon $diskon)
    {
        $diskon->delete();
        return back()->with('destroy', 'success');
    }

    public function toggle(Diskon $diskon)
    {
        $diskon->update(['aktif' => !$diskon->aktif]);
        return back()->with('toggle', 'success');
    }
}