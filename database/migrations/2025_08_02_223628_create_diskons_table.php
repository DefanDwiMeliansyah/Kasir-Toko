<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diskons', function (Blueprint $table) {
            $table->id();
            $table->string('kode_diskon')->unique();
            $table->string('nama_diskon');
            $table->text('deskripsi')->nullable();
            $table->enum('jenis_diskon', ['persen', 'nominal']);
            $table->decimal('nilai_diskon', 10, 2);
            $table->decimal('maksimal_diskon', 10, 2)->nullable();
            $table->decimal('minimal_belanja', 10, 2)->default(0);
            $table->enum('jenis_kondisi', ['semua', 'kategori', 'produk']);
            $table->json('kondisi_ids')->nullable(); // untuk menyimpan kategori_id atau produk_id
            $table->integer('kuota')->nullable();
            $table->integer('terpakai')->default(0);
            $table->datetime('tanggal_mulai');
            $table->datetime('tanggal_berakhir');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskons');
    }
};
