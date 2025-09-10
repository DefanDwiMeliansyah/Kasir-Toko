<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetilPenjualan;
use App\Models\pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
use App\Models\Diskon;
use Jackiedo\Cart\Cart;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $penjualans = Penjualan::join('users', 'users.id', 'penjualans.user_id')
            ->join('pelanggans', 'pelanggans.id', 'penjualans.pelanggan_id')
            ->select('penjualans.*', 'users.nama as nama_kasir', 'pelanggans.nama as nama_pelanggan')
            ->orderBy('id', 'desc')
            ->when($search, function ($q, $search) {
                return $q->where('nomor_transaksi', 'like', "%{$search}%");
            })
            ->paginate();

        if ($search) $penjualans->appends(['search' => $search]);

        return view('transaksi.index', [
            'penjualans' => $penjualans
        ]);
    }

    public function create(Request $request)
    {
        return view('transaksi.create', [
            'nama_kasir' => $request->user()->nama,
            'tanggal' => date('d F Y')
        ]);
    }

    public function store(Request $request, Cart $cart)
    {
        $request->validate([
            'pelanggan_id' => ['nullable', 'exists:pelanggans,id'],
            'cash' => ['required', 'numeric', 'gte:total_bayar'],
            'diskon_id' => ['nullable', 'exists:diskons,id']
        ], [], [
            'pelanggan_id' => 'pelanggan'
        ]);

        if (!$request->pelanggan_id) {
            $pelanggan = Pelanggan::create(['nama' => Pelanggan::generateDefaultName()]);
            $request->merge(['pelanggan_id' => $pelanggan->id]);
        }

        $user = $request->user();
        $lastPenjualan = Penjualan::orderBy('id', 'desc')->first();

        $cart = $cart->name($user->id);
        $cartDetails = $cart->getDetails();

        $subtotal = $cartDetails->get('subtotal');
        $taxAmount = $cartDetails->get('tax_amount');
        $total = $cartDetails->get('total');

        // TAMBAHAN: Handle diskon pelanggan 10%
        $diskonPelangganNominal = 0;
        $extraInfo = $cartDetails->get('extra_info');
        if ($extraInfo && isset($extraInfo['pelanggan'])) {
            $pelanggan = Pelanggan::find($extraInfo['pelanggan']['id']);
            if ($pelanggan) {
                $diskonPelangganNominal = $total * 0.10; // 10% diskon
            }
        }

        // Handle diskon dengan logik baru
        $diskonNominal = 0;
        $diskonId = null;
        $itemsDiskon = [];

        if ($request->diskon_id) {
            $diskon = Diskon::find($request->diskon_id);
            if ($diskon && $diskon->isValid()) {
                // Siapkan data items untuk validasi kondisi
                $items = [];
                $allItems = $cartDetails->get('items');
                foreach ($allItems as $key => $value) {
                    $item = $allItems->get($key);
                    $items[] = [
                        'id' => $item->id,
                        'title' => $item->title,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'hash' => $key // Gunakan key sebagai hash
                    ];
                }

                $hasilDiskon = $diskon->hitungDiskonBaru($items);
                $diskonNominal = $hasilDiskon['total_diskon'];
                $itemsDiskon = $hasilDiskon['items_diskon'];

                if ($diskonNominal > 0) {
                    $diskonId = $diskon->id;
                }
            }
        }

        // Hitung ulang total setelah diskon
        $finalTotal = $total - $diskonNominal - $diskonPelangganNominal;
        $kembalian = $request->cash - $finalTotal;

        $no = $lastPenjualan ? $lastPenjualan->id + 1 : 1;
        $no = sprintf("%04d", $no);


        // Cek stok terlebih dahulu
        $allItems = $cartDetails->get('items');
        foreach ($allItems as $key => $value) {
            $item = $allItems->get($key);
            $produk = Produk::find($item->id);

            if ($produk && $produk->stok < $item->quantity) {
                return redirect()
                    ->route('transaksi.create')
                    ->with('store', 'gagal')
                    ->with('produk_kurang', [$produk->nama_produk]);
            }
        }

        $penjualan = Penjualan::create([
            'user_id' => $user->id,
            'pelanggan_id' => $cart->getExtraInfo('pelanggan.id') ?: Pelanggan::create(['nama' => Pelanggan::generateDefaultName()])->id,
            'nomor_transaksi' => date('Ymd') . $no,
            'tanggal' => date('Y-m-d H:i:s'),
            'total' => $finalTotal,
            'tunai' => $request->cash,
            'kembalian' => $kembalian,
            'pajak' => $taxAmount,
            'subtotal' => $subtotal,
            'diskon_id' => $diskonId,
            'diskon_nominal' => $diskonNominal,
            'diskon_detail' => !empty($itemsDiskon) ? json_encode($itemsDiskon) : null, // Simpan detail diskon per item
            'diskon_pelanggan_nominal' => $diskonPelangganNominal // TAMBAHAN: Simpan diskon pelanggan
        ]);

        foreach ($allItems as $key => $value) {
            $item = $allItems->get($key);

            // Cari diskon untuk item ini
            $diskonItemNominal = 0;
            $hash = $key; // Gunakan key sebagai hash
            if (isset($itemsDiskon[$hash])) {
                $diskonItemNominal = $itemsDiskon[$hash]['diskon_nominal'];
            }

            DetilPenjualan::create([
                'penjualan_id' => $penjualan->id,
                'produk_id' => $item->id,
                'jumlah' => $item->quantity,
                'harga_produk' => $item->price,
                'subtotal' => $item->subtotal,
                'diskon_nominal' => $diskonItemNominal, // Simpan diskon per item
                'subtotal_setelah_diskon' => $item->subtotal - $diskonItemNominal
            ]);

            // Update stok
            $produk = Produk::find($item->id);
            if ($produk) {
                $produk->stok -= $item->quantity;
                $produk->save();
            }
        }

        // Increment penggunaan diskon jika ada
        if ($diskonId && $diskonNominal > 0) {
            $diskon = Diskon::find($diskonId);
            if ($diskon) {
                $diskon->incrementTerpakai();
            }
        }

        $cart->destroy();

        return redirect()->route('transaksi.show', ['transaksi' => $penjualan->id]);
    }

    public function show(Request $request, Penjualan $transaksi)
    {
        $pelanggan = pelanggan::find($transaksi->pelanggan_id);
        $user = User::find($transaksi->user_id);
        $detilPenjualan = DetilPenjualan::join('produks', 'produks.id', 'detil_penjualans.produk_id')
            ->select('detil_penjualans.*', 'nama_produk')
            ->where('penjualan_id', $transaksi->id)->get();

        $diskon = null;
        if ($transaksi->diskon_id) {
            $diskon = Diskon::find($transaksi->diskon_id);
        }

        return view('transaksi.invoice', [
            'penjualan' => $transaksi,
            'pelanggan' => $pelanggan,
            'user' => $user,
            'detilPenjualan' => $detilPenjualan,
            'diskon' => $diskon
        ]);
    }

    public function destroy(Request $request, Penjualan $transaksi)
    {
        // Cek jika sudah dibatalkan sebelumnya
        if ($transaksi->status == 'batal') {
            return back()->with('destroy', 'success');
        }

        // Ambil semua detil penjualan
        $detil = DetilPenjualan::where('penjualan_id', $transaksi->id)->get();

        foreach ($detil as $item) {
            // Kembalikan stok
            $produk = Produk::find($item->produk_id);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->save();
            }
        }

        // Kembalikan kuota diskon jika ada
        if ($transaksi->diskon_id) {
            $diskon = Diskon::find($transaksi->diskon_id);
            if ($diskon) {
                $diskon->decrementTerpakai();
            }
        }

        // Ubah status menjadi batal
        $transaksi->update([
            'status' => 'batal'
        ]);

        return back()->with('destroy', 'success');
    }

    public function produk(Request $request)
    {
        $search = $request->search;
        $produks = Produk::with('kategori')  // tambahkan ini
            ->select('id', 'kode_produk', 'nama_produk')
            ->when($search, function ($q, $search) {
                return $q->where('nama_produk', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($subQ) use ($search) {  // tambahkan ini
                        $subQ->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->orderBy('nama_produk')
            ->take(15)
            ->get();

        return response()->json($produks);
    }

    public function pelanggan(Request $request)
    {
        $search = $request->search;
        $pelanggans = Pelanggan::select('id', 'nama')
            ->where('nama', 'not like', 'pembeli-%') // Only show non-default customers
            ->when($search, function ($q, $search) {
                return $q->where('nama', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->take(15)
            ->get();

        return response()->json($pelanggans);
    }

    public function addPelanggan(Request $request, Cart $cart)
    {
        $request->validate([
            'id' => ['required', 'exists:pelanggans,id']
        ]);
        $pelanggan = Pelanggan::find($request->id);

        $cart = $cart->name($request->user()->id);

        $cart->setExtraInfo([
            'pelanggan' => [
                'id' => $pelanggan->id,
                'nama' => $pelanggan->nama,
            ]
        ]);

        return response()->json(['message' => 'Berhasil.']);
    }

    public function cetak(Request $request)
    {
        $penjualan = Penjualan::find($request->transaksi);

        if (!$penjualan) {
            abort(404, 'Transaksi tidak ditemukan');
        }

        // Ambil relasi user dan pelanggan berdasarkan penjualan
        $pelanggan = Pelanggan::find($penjualan->pelanggan_id);
        $user = User::find($penjualan->user_id);

        $detilPenjualan = DetilPenjualan::join('produks', 'produks.id', '=', 'detil_penjualans.produk_id')
            ->where('penjualan_id', $penjualan->id)
            ->select('detil_penjualans.*', 'nama_produk')
            ->get();

        $diskon = null;
        if ($penjualan->diskon_id) {
            $diskon = Diskon::find($penjualan->diskon_id);
        }

        return view('transaksi.cetak', compact('penjualan', 'pelanggan', 'user', 'detilPenjualan', 'diskon'));
    }
}
