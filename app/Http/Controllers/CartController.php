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

        $cartDetails = $cart->getDetails();
        $response = $cartDetails->toArray();

        // Handle diskon pelanggan 10%
        $extraInfo = $cartDetails->get('extra_info');
        $diskonPelanggan = 0;
        if ($extraInfo && isset($extraInfo['pelanggan'])) {
            $pelanggan = Pelanggan::find($extraInfo['pelanggan']['id']);
            if ($pelanggan) {
                $diskonPelanggan = $cartDetails->get('total') * 0.10;
                $response['extra_info']['diskon_pelanggan'] = [
                    'nominal' => $diskonPelanggan,
                    'nama' => 'Diskon Pelanggan'
                ];
            }
        }

        // Jika ada diskon, hitung ulang dengan logika baru
        if ($extraInfo && isset($extraInfo['diskon'])) {
            $diskon = Diskon::find($extraInfo['diskon']['id']);
            if ($diskon) {
                // Siapkan data items untuk perhitungan diskon
                $items = [];
                foreach ($cartDetails->get('items') as $key => $cartItem) {
                    $items[] = [
                        'id' => $cartItem->id,
                        'title' => $cartItem->title,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                        'hash' => $cartItem->hash
                    ];
                }

                // Hitung diskon dengan logika baru
                $hasilDiskon = $diskon->hitungDiskonBaru($items);

                // Cek apakah diskon masih berlaku
                if (!$diskon->isBerlakuUntukCart($items) || $hasilDiskon['total_diskon'] == 0) {
                    // Auto-remove diskon jika tidak berlaku
                    $cart->setExtraInfo([
                        'pelanggan' => $extraInfo['pelanggan'] ?? null
                    ]);
                    $response['extra_info']['diskon_auto_removed'] = true;
                } else {
                    // Update informasi diskon di response
                    $response['extra_info']['diskon'] = [
                        'id' => $diskon->id,
                        'kode' => $diskon->kode_diskon,
                        'nama' => $diskon->nama_diskon,
                        'nominal' => $hasilDiskon['total_diskon'],
                        'items_diskon' => $hasilDiskon['items_diskon']
                    ];

                    // Update total dengan diskon
                    $response['total'] = $cartDetails->get('total') - $hasilDiskon['total_diskon'] - $diskonPelanggan;
                }
            }
        } else {
            // Update total dengan diskon pelanggan saja (jika ada)
            if ($diskonPelanggan > 0) {
                $response['total'] = $cartDetails->get('total') - $diskonPelanggan;
            }
        }

        return response()->json($response);
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
            'quantity' => $quantity,
            'price' => $produk->harga
        ]);

        // Revalidasi diskon setelah menambah item
        $this->revalidateDiscount($cart);

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

        $newQuantity = $item->getQuantity() + $request->qty;

        if ($newQuantity <= 0) {
            // Jika quantity jadi 0 atau negatif, hapus item
            $cart->removeItem($hash);
        } else {
            $cart->updateItem($item->getHash(), [
                'quantity' => $newQuantity
            ]);
        }

        // Revalidasi diskon setelah update
        $this->revalidateDiscount($cart);

        return response()->json(['message' => 'Berhasil diupdate.']);
    }

    public function destroy(Request $request, $hash)
    {
        $cart = (new Cart)->name($request->user()->id);
        $cart->removeItem($hash);

        // Revalidasi diskon setelah hapus item
        $this->revalidateDiscount($cart);

        return response()->json(['message' => 'Berhasil di hapus.']);
    }

    public function clear(Request $request)
    {
        $cart = (new Cart)->name($request->user()->id);
        $cart->destroy();

        return back();
    }

    /**
     * PERBAIKAN: Method untuk revalidasi diskon yang lebih lengkap
     */
    private function revalidateDiscount($cart)
    {
        $cartDetails = $cart->getDetails();
        $extraInfo = $cartDetails->get('extra_info');

        // Cek apakah ada diskon yang perlu direvalidasi
        if ($extraInfo && isset($extraInfo['diskon'])) {
            $diskon = Diskon::find($extraInfo['diskon']['id']);

            if ($diskon) {
                // Siapkan data items untuk validasi
                $items = [];
                foreach ($cartDetails->get('items') as $cartItem) {
                    $items[] = [
                        'id' => $cartItem->id,
                        'title' => $cartItem->title,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                        'hash' => $cartItem->hash
                    ];
                }

                $shouldRemoveDiscount = false;

                // Cek apakah cart kosong
                if (empty($items)) {
                    $shouldRemoveDiscount = true;
                }

                // Cek validitas diskon (aktif, belum expired, dll)
                if (!$shouldRemoveDiscount && !$diskon->isValid()) {
                    $shouldRemoveDiscount = true;
                }

                // PERBAIKAN: Cek maksimal_jumlah_produk dengan lebih teliti
                if (!$shouldRemoveDiscount && $diskon->maksimal_jumlah_produk) {
                    $itemsYangBerlaku = [];
                    
                    foreach ($items as $item) {
                        $berlaku = false;

                        if ($diskon->jenis_kondisi === 'semua') {
                            $berlaku = true;
                        } elseif ($diskon->jenis_kondisi === 'produk') {
                            $berlaku = in_array($item['id'], $diskon->kondisi_ids ?? []);
                        } elseif ($diskon->jenis_kondisi === 'kategori') {
                            $produk = \App\Models\Produk::find($item['id']);
                            if ($produk && $produk->kategori_id) {
                                $berlaku = in_array($produk->kategori_id, $diskon->kondisi_ids ?? []);
                            }
                        }

                        if ($berlaku) {
                            $itemsYangBerlaku[] = $item;
                        }
                    }

                    $totalJumlahBerlaku = array_sum(array_column($itemsYangBerlaku, 'quantity'));
                    
                    // Debug: Uncomment untuk debugging
                    // \Log::info("Total jumlah berlaku: {$totalJumlahBerlaku}, Maksimal: {$diskon->maksimal_jumlah_produk}");
                    
                    if ($totalJumlahBerlaku > $diskon->maksimal_jumlah_produk) {
                        $shouldRemoveDiscount = true;
                    }
                }

                // Cek minimal belanja
                if (!$shouldRemoveDiscount) {
                    $totalKeseluruhan = array_sum(array_map(function($item) {
                        return $item['price'] * $item['quantity'];
                    }, $items));
                    
                    if ($totalKeseluruhan < $diskon->minimal_belanja) {
                        $shouldRemoveDiscount = true;
                    }
                }

                // Cek apakah ada item yang berlaku untuk diskon
                if (!$shouldRemoveDiscount && !$diskon->isBerlakuUntukCart($items)) {
                    $shouldRemoveDiscount = true;
                }

                // HAPUS DISKON JIKA TIDAK MEMENUHI SYARAT
                if ($shouldRemoveDiscount) {
                    // Hapus diskon dari cart
                    $newExtraInfo = [];
                    if (isset($extraInfo['pelanggan'])) {
                        $newExtraInfo['pelanggan'] = $extraInfo['pelanggan'];
                    }
                    $cart->setExtraInfo($newExtraInfo);
                    
                    // Debug: Uncomment untuk debugging
                    // \Log::info("Diskon dihapus karena tidak memenuhi syarat");
                } else {
                    // Update diskon dengan perhitungan baru
                    $hasilDiskon = $diskon->hitungDiskonBaru($items);

                    if ($hasilDiskon['total_diskon'] > 0) {
                        $newExtraInfo = [
                            'diskon' => [
                                'id' => $diskon->id,
                                'kode' => $diskon->kode_diskon,
                                'nama' => $diskon->nama_diskon,
                                'nominal' => $hasilDiskon['total_diskon'],
                                'items_diskon' => $hasilDiskon['items_diskon']
                            ]
                        ];
                        if (isset($extraInfo['pelanggan'])) {
                            $newExtraInfo['pelanggan'] = $extraInfo['pelanggan'];
                        }
                        $cart->setExtraInfo($newExtraInfo);
                    } else {
                        // Jika diskon jadi 0, hapus diskon
                        $newExtraInfo = [];
                        if (isset($extraInfo['pelanggan'])) {
                            $newExtraInfo['pelanggan'] = $extraInfo['pelanggan'];
                        }
                        $cart->setExtraInfo($newExtraInfo);
                    }
                }
            }
        }
    }

    public function applyDiscount(Request $request)
    {
        // PERBAIKAN: Validasi input kosong dengan pesan yang lebih baik
        $request->validate([
            'kode_diskon' => ['required', 'string', 'min:1']
        ], [
            'kode_diskon.required' => 'Kode diskon wajib diisi.',
            'kode_diskon.min' => 'Kode diskon tidak boleh kosong.'
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
        $diskon = Diskon::where('kode_diskon', strtoupper(trim($request->kode_diskon)))->first();

        if (!$diskon) {
            return response()->json([
                'success' => false,
                'message' => 'Kode diskon tidak ditemukan.'
            ], 404);
        }

        // Validasi diskon
        if (!$diskon->isValid()) {
            $status = $diskon->status;
            $message = match ($status) {
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
        foreach ($cartDetails->get('items') as $key => $cartItem) {
            $items[] = [
                'id' => $cartItem->id,
                'title' => $cartItem->title,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->price,
                'hash' => $cartItem->hash
            ];
        }

        // PERBAIKAN: Validasi maksimal_jumlah_produk SEBELUM hitung diskon
        if ($diskon->maksimal_jumlah_produk) {
            $itemsYangBerlaku = [];
            foreach ($items as $item) {
                $berlaku = false;

                if ($diskon->jenis_kondisi === 'semua') {
                    $berlaku = true;
                } elseif ($diskon->jenis_kondisi === 'produk') {
                    $berlaku = in_array($item['id'], $diskon->kondisi_ids ?? []);
                } elseif ($diskon->jenis_kondisi === 'kategori') {
                    $produk = \App\Models\Produk::find($item['id']);
                    if ($produk && $produk->kategori_id) {
                        $berlaku = in_array($produk->kategori_id, $diskon->kondisi_ids ?? []);
                    }
                }

                if ($berlaku) {
                    $itemsYangBerlaku[] = $item;
                }
            }

            $totalJumlah = array_sum(array_column($itemsYangBerlaku, 'quantity'));
            if ($totalJumlah > $diskon->maksimal_jumlah_produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maksimal ' . $diskon->maksimal_jumlah_produk . ' item untuk diskon ini.'
                ], 400);
            }
        }

        // Validasi minimal_belanja
        $totalKeseluruhan = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $items));

        if ($totalKeseluruhan < $diskon->minimal_belanja) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal belanja Rp ' . number_format($diskon->minimal_belanja, 0, ',', '.') . ' untuk diskon ini.'
            ], 400);
        }

        // Hitung diskon dengan logika baru (setelah semua validasi berhasil)
        $hasilDiskon = $diskon->hitungDiskonBaru($items);

        if ($hasilDiskon['total_diskon'] == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Diskon tidak dapat diterapkan pada produk di keranjang.'
            ], 400);
        }

        // Terapkan diskon ke cart
        $cart->setExtraInfo([
            'diskon' => [
                'id' => $diskon->id,
                'kode' => $diskon->kode_diskon,
                'nama' => $diskon->nama_diskon,
                'nominal' => $hasilDiskon['total_diskon'],
                'items_diskon' => $hasilDiskon['items_diskon']
            ],
            'pelanggan' => $cartDetails->get('extra_info')['pelanggan'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Diskon berhasil diterapkan!',
            'diskon' => [
                'kode' => $diskon->kode_diskon,
                'nama' => $diskon->nama_diskon,
                'nominal' => $hasilDiskon['total_diskon'],
                'nominal_formatted' => 'Rp ' . number_format($hasilDiskon['total_diskon'], 0, ',', '.'),
                'items_diskon' => $hasilDiskon['items_diskon']
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

        // Apply tax ulang dan ambil data cart yang sudah diperbarui
        $cart->applyTax([
            'id' => 1,
            'rate' => 10,
            'title' => 'Pajak PPN 10%'
        ]);

        // Ambil cart details yang sudah diperbarui (tanpa diskon)
        $updatedCartDetails = $cart->getDetails();
        $response = $updatedCartDetails->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Diskon berhasil dihapus.',
            'cart_data' => $response
        ]);
    }
}