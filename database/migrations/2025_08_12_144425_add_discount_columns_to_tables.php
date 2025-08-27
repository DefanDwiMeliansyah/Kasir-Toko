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
        // Tambah kolom di tabel penjualans untuk menyimpan detail diskon per item
        Schema::table('penjualans', function (Blueprint $table) {
            $table->text('diskon_detail')->nullable()->after('diskon_nominal');
        });

        // Tambah kolom di tabel detil_penjualans untuk menyimpan diskon per item
        Schema::table('detil_penjualans', function (Blueprint $table) {
            $table->decimal('diskon_nominal', 15, 2)->default(0)->after('subtotal');
            $table->decimal('subtotal_setelah_diskon', 15, 2)->nullable()->after('diskon_nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropColumn('diskon_detail');
        });

        Schema::table('detil_penjualans', function (Blueprint $table) {
            $table->dropColumn(['diskon_nominal', 'subtotal_setelah_diskon']);
        });
    }
};