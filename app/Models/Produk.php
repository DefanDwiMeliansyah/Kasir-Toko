<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'harga',
        'harga_beli',
        'kategori_id',
        'stok',
    ];

    /**
     * Relasi ke kategori
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Scope untuk pencarian produk berdasarkan nama atau kategori
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama_produk', 'like', "%{$search}%")
            ->orWhereHas('kategori', function ($q) use ($search) {
                $q->where('nama_kategori', 'like', "%{$search}%");
            });
    }
}
