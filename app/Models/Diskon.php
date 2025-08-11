<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    public function hitungDiskon($subtotal, $cartItems = [])
    {
        // Cek minimal belanja
        if ($subtotal <  $this->minimal_belanja) {
            return 0;
        }

        // Cek kondisi berdasarkan jenis
        if ($this->jenis_kondisi !== 'semua') {
            $valid = $this->validateKondisi($cartItems);
            if (!$valid) {
                return 0;
            }
        }

        // Hitung diskon
        if ($this->jenis_diskon === 'persen') {
            $diskon = ($subtotal * $this->nilai_diskon) / 100;
            
            // Batasi maksimal diskon jika ada
            if ($this->maksimal_diskon && $diskon > $this->maksimal_diskon) {
                $diskon = $this->maksimal_diskon;
            }
        } else {
            $diskon = $this->nilai_diskon;
        }

        return min($diskon, $subtotal);
    }

    private function validateKondisi($cartItems)
    {
        if (empty($cartItems) || empty($this->kondisi_ids)) {
            return false;
        }

        foreach ($cartItems as $item) {
            if ($this->jenis_kondisi === 'kategori') {
                $produk = Produk::find($item['id']);
                if ($produk && in_array($produk->kategori_id, $this->kondisi_ids)) {
                    return true;
                }
            } elseif ($this->jenis_kondisi === 'produk') {
                if (in_array($item['id'], $this->kondisi_ids)) {
                    return true;
                }
            }
        }

        return false;
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
