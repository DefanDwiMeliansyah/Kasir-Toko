<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Produk; // TAMBAHAN: Import model Produk

class Diskon extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_diskon',
        'nama_diskon',
        'deskripsi',
        'jenis_diskon',
        'nilai_diskon',
        'maksimal_diskon',
        'minimal_belanja',
        'jenis_kondisi',
        'kondisi_ids',
        'kuota',
        'terpakai',
        'tanggal_mulai',
        'tanggal_berakhir',
        'aktif'
    ];

    protected $casts = [
        'kondisi_ids' => 'array',
        'tanggal_mulai' => 'datetime',
        'tanggal_berakhir' => 'datetime',
        'aktif' => 'boolean'
    ];

    public function isValid()
    {
        $now = Carbon::now();
        
        return $this->aktif 
            && $now->between($this->tanggal_mulai, $this->tanggal_berakhir)
            && ($this->kuota === null || $this->terpakai < $this->kuota);
    }

    /**
     * Hitung diskon untuk cart items dengan logika baru
     * Return: ['total_diskon' => nominal, 'items_diskon' => array per item]
     */
    public function hitungDiskonBaru($cartItems = [])
    {
        $result = [
            'total_diskon' => 0,
            'items_diskon' => [],
            'subtotal_berlaku' => 0 // Subtotal yang berlaku untuk diskon
        ];

        if (empty($cartItems)) {
            return $result;
        }

        // Kumpulkan item yang memenuhi kondisi diskon
        $itemsYangBerlaku = $this->getItemsYangBerlaku($cartItems);
        
        if (empty($itemsYangBerlaku)) {
            return $result;
        }

        // Hitung subtotal dari item yang berlaku
        $subtotalBerlaku = array_sum(array_column($itemsYangBerlaku, 'subtotal'));
        $result['subtotal_berlaku'] = $subtotalBerlaku;

        // Cek minimal belanja berdasarkan subtotal yang berlaku atau total keseluruhan
        $totalKeseluruhan = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cartItems));
        
        if ($totalKeseluruhan < $this->minimal_belanja) {
            return $result;
        }

        // Hitung diskon per item yang berlaku
        foreach ($itemsYangBerlaku as $item) {
            $diskonItem = $this->hitungDiskonPerItem($item, $subtotalBerlaku);
            
            if ($diskonItem > 0) {
                $result['items_diskon'][$item['hash']] = [
                    'produk_id' => $item['id'],
                    'nama_produk' => $item['title'],
                    'diskon_nominal' => $diskonItem,
                    'subtotal_asli' => $item['subtotal'],
                    'subtotal_setelah_diskon' => $item['subtotal'] - $diskonItem
                ];
                
                $result['total_diskon'] += $diskonItem;
            }
        }

        return $result;
    }

    /**
     * Mendapatkan item yang memenuhi kondisi diskon
     */
    private function getItemsYangBerlaku($cartItems)
    {
        $itemsBerlaku = [];

        foreach ($cartItems as $item) {
            $berlaku = false;

            if ($this->jenis_kondisi === 'semua') {
                $berlaku = true;
            } elseif ($this->jenis_kondisi === 'produk') {
                $berlaku = in_array($item['id'], $this->kondisi_ids ?? []);
            } elseif ($this->jenis_kondisi === 'kategori') {
                $produk = Produk::find($item['id']);
                if ($produk && $produk->kategori_id) {
                    $berlaku = in_array($produk->kategori_id, $this->kondisi_ids ?? []);
                }
            }

            if ($berlaku) {
                $item['subtotal'] = $item['price'] * $item['quantity'];
                $itemsBerlaku[] = $item;
            }
        }

        return $itemsBerlaku;
    }

    /**
     * Hitung diskon untuk item individual
     */
    private function hitungDiskonPerItem($item, $totalSubtotalBerlaku)
    {
        $subtotalItem = $item['subtotal'];

        if ($this->jenis_diskon === 'persen') {
            $diskon = ($subtotalItem * $this->nilai_diskon) / 100;
            
            // Jika ada maksimal diskon, distribusikan proporsional
            if ($this->maksimal_diskon && $totalSubtotalBerlaku > 0) {
                $proporsi = $subtotalItem / $totalSubtotalBerlaku;
                $maxDiskonItem = $this->maksimal_diskon * $proporsi;
                $diskon = min($diskon, $maxDiskonItem);
            }
        } else {
            // Untuk diskon nominal, distribusikan proporsional
            if ($totalSubtotalBerlaku > 0) {
                $proporsi = $subtotalItem / $totalSubtotalBerlaku;
                $diskon = $this->nilai_diskon * $proporsi;
            } else {
                $diskon = 0;
            }
        }

        return min($diskon, $subtotalItem);
    }

    /**
     * Cek apakah diskon masih berlaku untuk cart items saat ini
     * Digunakan untuk validasi realtime
     */
    public function isBerlakuUntukCart($cartItems)
    {
        if (!$this->isValid()) {
            return false;
        }

        $itemsBerlaku = $this->getItemsYangBerlaku($cartItems);
        return !empty($itemsBerlaku);
    }

    // Method lama untuk backward compatibility
    public function hitungDiskon($subtotal, $cartItems = [])
    {
        $hasil = $this->hitungDiskonBaru($cartItems);
        return $hasil['total_diskon'];
    }

    private function validateKondisi($cartItems)
    {
        $itemsBerlaku = $this->getItemsYangBerlaku($cartItems);
        return !empty($itemsBerlaku);
    }

    public function incrementTerpakai()
    {
        $this->increment('terpakai');
    }

    public function decrementTerpakai()
    {
        if ($this->terpakai > 0) {
            $this->decrement('terpakai');
        }
    }

    public function getStatusAttribute()
    {
        if (!$this->aktif) return 'Tidak Aktif';
        
        $now = Carbon::now();
        if ($now < $this->tanggal_mulai) return 'Belum Dimulai';
        if ($now > $this->tanggal_berakhir) return 'Expired';
        if ($this->kuota && $this->terpakai >= $this->kuota) return 'Kuota Habis';
        
        return 'Aktif';
    }
}