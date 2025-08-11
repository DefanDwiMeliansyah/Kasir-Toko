<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Diskon;
use Jackiedo\Cart\Cart;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = (new Cart)->name($request->user()->id);

        $cart->applyTax([
            'id' => 1,
            'rate' => 10,
            'title' => 'Pajak PPN 10%'
        ]);

        return $cart->getDetails()->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => ['required', 'exists:produks,kode_produk'],
            'quantity' => ['sometimes', 'integer', 'min:1'] 
        ]);

        $produk = Produk::where('kode_produk', $request->kode_produk)->first();

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $cart = (new Cart)->name($request->user()->id);
        
        // Gunakan quantity dari request, default 1 jika tidak ada
        $quantity = $request->input('quantity', 1);

        $cart->addItem([
            'id' => $produk->id,
            'title' => $produk->nama_produk,
            'quantity' => $quantity, // Gunakan quantity yang dikirim dari form
            'price' => $produk->harga
        ]);

        return response()->json(['message' => 'Berhasil ditambahkan.']);
    }

    public function update(Request $request, $hash)
    {
        $request->validate([
            'qty' => ['required', 'in:-1,1']
        ]);

        $cart = (new Cart)->name($request->user()->id);
        $item = $cart->getItem($hash);

        if (!$item) {
            return abort(404);
        }

        $cart->updateItem($item->getHash(), [
            'quantity' => $item->getQuantity() + $request->qty
        ]);

        return response()->json(['message' => 'Berhasil diupdate.']);
    }

    public function destroy(Request $request, $hash)
    {
        $cart = (new Cart)->name($request->user()->id);
        $cart->removeItem($hash);

        return response()->json(['message' => 'Berhasil dihapus.']);
    }

    public function clear(Request $request)
    {
        $cart = (new Cart)->name($request->user()->id);
        $cart->destroy();

        return back();
    }

        public function applyDiscount(Request $request)
    {
        $request->validate([
            'kode_diskon' => ['required', 'string']
        ]);

        $cart = (new Cart)->name($request->user()->id);
        $cartDetails = $cart->getDetails();
        
        // Cek apakah cart kosong
        if ($cartDetails->get('items')->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang belanja masih kosong.'
            ], 400);
        }

        // Cari diskon berdasarkan kode
        $diskon = Diskon::where('kode_diskon', strtoupper($request->kode_diskon))->first();

        if (!$diskon) {
            return response()->json([
                'success' => false,
                'message' => 'Kode diskon tidak ditemukan.'
            ], 404);
        }

        // Validasi diskon
        if (!$diskon->isValid()) {
            $status = $diskon->status;
            $message = match($status) {
                'Tidak Aktif' => 'Kode diskon sedang tidak aktif.',
                'Expired' => 'Kode diskon sudah kedaluwarsa.',
                'Belum Dimulai' => 'Kode diskon belum dapat digunakan.',
                'Kuota Habis' => 'Kuota penggunaan diskon sudah habis.',
                default => 'Kode diskon tidak dapat digunakan.'
            };

            return response()->json([
                'success' => false,
                'message' => $message
            ], 400);
        }

        // Siapkan data items untuk validasi kondisi
        $items = [];
        foreach ($cartDetails->get('items') as $key => $item) {
            $cartItem = $cartDetails->get('items')->get($key);
            $items[] = [
                'id' => $cartItem->id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->price 
            ];
        }

        // Hitung diskon
        $subtotal = $cartDetails->get('subtotal');
        $nominalDiskon = $diskon->hitungDiskon($subtotal, $items);

        if ($nominalDiskon == 0) {
            if ($subtotal <= $diskon->minimal_belanja) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal belanja untuk diskon ini adalah Rp ' . number_format($diskon->minimal_belanja, 0, ',', '.')
                ], 400);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Diskon tidak dapat diterapkan pada produk di keranjang Anda.'
                ], 400);
            }
        }

        // Terapkan diskon ke cart sebagai discount
        $cart->setExtraInfo([
            'diskon' => [
                'id' => $diskon->id,
                'kode' => $diskon->kode_diskon,
                'nama' => $diskon->nama_diskon,
                'nominal' => $nominalDiskon
            ],
            'pelanggan' => $cartDetails->get('extra_info')['pelanggan'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Diskon berhasil diterapkan!',
            'diskon' => [
                'kode' => $diskon->kode_diskon,
                'nama' => $diskon->nama_diskon,
                'nominal' => $nominalDiskon,
                'nominal_formatted' => 'Rp ' . number_format($nominalDiskon, 0, ',', '.')
            ]
        ]);
    }

    public function removeDiscount(Request $request)
    {
        $cart = (new Cart)->name($request->user()->id);
        $cartDetails = $cart->getDetails();
        
        // Hapus diskon dari extra_info
        $cart->setExtraInfo([
            'pelanggan' => $cartDetails->get('extra_info')['pelanggan'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Diskon berhasil dihapus.'
        ]);
    }
}