<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $pelanggans = Pelanggan::orderBy('id')
            ->when($search, fn($q) => $q->where('nama', 'like', "%{$search}%"))
            ->paginate();

        if ($search) {
            $pelanggans->append(['search' => $search]);
        }

        return view('pelanggan.index', compact('pelanggans'));
    }

    public function create()
    {
        return view('pelanggan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['nullable', 'max:100'], // Ubah dari required ke nullable
            'alamat' => ['nullable', 'max:500'],
            'nomor_tlp' => ['nullable', 'max:14'],
        ]);

        $data = $request->all();
        
        // Jika nama kosong, generate nama default
        if (empty($data['nama'])) {
            $data['nama'] = Pelanggan::generateDefaultName();
        }

        Pelanggan::create($data);

        return redirect()->route('pelanggan.index')->with('store', 'success');
    }

    public function show(Pelanggan $pelanggan)
    {
        abort(404);
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $request->validate([
            'nama' => ['nullable', 'max:100'], // Ubah dari required ke nullable
            'alamat' => ['nullable', 'max:500'],
            'nomor_tlp' => ['nullable', 'max:14'],
        ]);

        $data = $request->all();
        
        // Jika nama kosong, generate nama default
        if (empty($data['nama'])) {
            $data['nama'] = Pelanggan::generateDefaultName();
        }

        $pelanggan->update($data);

        return redirect()->route('pelanggan.index')->with('update', 'success');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();
        return back()->with('destroy', 'success');
    }
}