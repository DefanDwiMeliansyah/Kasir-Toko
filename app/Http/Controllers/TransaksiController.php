<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetilPenjualan;
use App\Models\pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
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
            'pelanggan_id' => ['required', 'exists:pelanggans,id'],
            'cash' => ['required', 'numeric', 'gte:total_bayar']
        ], [], [
            'pelanggan_id' => 'pelanggan'
        ]);

        $user = $request->user();
        $lastPenjualan = Penjualan::orderBy('id', 'desc')->first();

        $cart = $cart->name($user->id);
        $cartDetails = $cart->getDetails();

        $total = $cartDetails->get('total');
        $kembalian = $request->cash - $total;

        $no = $lastPenjualan ? $lastPenjualan->id + 1 : 1;
        $no = sprintf("%04d", $no);

        $penjualan = Penjualan::create([
            'user_id' => $user->id,
            'pelanggan_id' => $cart->getExtraInfo('pelanggan.id'),
            'nomor_transaksi' => date('Ymd') . $no,
            'tanggal' => date('Y-m-d H:i:s'),
            'total' => $total,
            'tunai' => $request->cash,
            'kembalian' => $kembalian,
            'pajak' => $cartDetails->get('tax_amount'),
            'subtotal' => $cartDetails->get('subtotal')
        ]);

        $allItems = $cartDetails->get('items');

        foreach ($allItems as $key => $value) {
            $item = $allItems->get($key);

            DetilPenjualan::create([
                'penjualan_id' => $penjualan->id,
                'produk_id' => $item->id,
                'jumlah' => $item->quantity,
                'harga_produk' => $item->price,
                'subtotal' => $item->subtotal,
            ]);

            $produk = Produk::find($item->id);
if ($produk) {
    if ($produk->stok < $item->quantity) {
        // Kirim nama produk ke session
        return redirect()
            ->route('transaksi.create')
            ->with('store', 'gagal')
            ->with('produk_kurang', [$produk->nama]);
    }

    $produk->stok -= $item->quantity;
    $produk->save();
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

        return view('transaksi.invoice', [
            'penjualan' => $transaksi,
            'pelanggan' => $pelanggan,
            'user' => $user,
            'detilPenjualan' => $detilPenjualan
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

        // Ubah status menjadi batal
        $transaksi->update([
            'status' => 'batal'
        ]);

        return back()->with('destroy', 'success');
    }

    public function produk(Request $request)
    {
        $search = $request->search;
        $produks = Produk::select('id', 'kode_produk', 'nama_produk')
            ->when($search, function ($q, $search) {
                return $q->where('nama_produk', 'like', "%{$search}%");
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

        return view('transaksi.cetak', compact('penjualan', 'pelanggan', 'user', 'detilPenjualan'));
    }
}
