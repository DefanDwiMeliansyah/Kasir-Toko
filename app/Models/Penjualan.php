<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'pelanggan_id',
        'nomor_transaksi',
        'tanggal',
        'total',
        'tunai',
        'kembalian',
        'status',
        'subtotal',
        'pajak',
        'diskon_id',
        'diskon_nominal',
        'diskon_detail', // Tambahan kolom untuk menyimpan detail diskon per item
        'diskon_pelanggan_nominal' // TAMBAHAN: Kolom untuk menyimpan diskon pelanggan
    ];

    protected $casts = [
        'diskon_detail' => 'array', // Cast sebagai array untuk mempermudah akses
    ];

    public function diskon()
    {
        return $this->belongsTo(Diskon::class, 'diskon_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function detilPenjualan()
    {
        return $this->hasMany(DetilPenjualan::class);
    }

    /**
     * Mendapatkan detail diskon per item
     * @return array
     */
    public function getDiskonDetailAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set detail diskon per item
     * @param array $value
     */
    public function setDiskonDetailAttribute($value)
    {
        $this->attributes['diskon_detail'] = is_array($value) ? json_encode($value) : $value;
    }
}