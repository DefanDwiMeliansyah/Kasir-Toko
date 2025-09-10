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
        Schema::table('diskons', function (Blueprint $table) {
            $table->integer('maksimal_jumlah_produk')->nullable()->after('minimal_belanja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diskons', function (Blueprint $table) {
            $table->dropColumn('maksimal_jumlah_produk');
        });
    }
};