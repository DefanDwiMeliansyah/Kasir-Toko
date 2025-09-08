<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'alamat',
        'nomor_tlp',
    ];

    /**
     * Generate nama default untuk pelanggan tanpa nama
     * Format: pelanggan-ddmmyyyy-hhmm-xxxx
     * 
     * @return string
     */
    public static function generateDefaultName()
    {
        $tanggal = date('dmY'); // format ddmmyyyy
        $jam = date('Hi'); // format hhmm
        
        // Cari nomor urut terakhir untuk hari ini
        $pattern = "Pembeli-{$tanggal}-{$jam}-%";
        $lastPelanggan = self::where('nama', 'like', $pattern)
            ->orderBy('nama', 'desc')
            ->first();
        
        $urutan = 1;
        if ($lastPelanggan) {
            // Extract nomor urut dari nama terakhir
            $namaParts = explode('-', $lastPelanggan->nama);
            if (count($namaParts) >= 4) {
                $lastUrutan = (int) end($namaParts);
                $urutan = $lastUrutan + 1;
            }
        }
        
        // Format nomor urut dengan 4 digit (0001, 0002, dst)
        $nomorUrut = str_pad($urutan, 4, '0', STR_PAD_LEFT);
        
        return "Pembeli-{$tanggal}-{$jam}-{$nomorUrut}";
    }
}