<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('nama_suplier');
            $table->unsignedInteger('jumlah');
            $table->integer('stok')->default(0);
            $table->date('tanggal');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stoks');
    }
};
