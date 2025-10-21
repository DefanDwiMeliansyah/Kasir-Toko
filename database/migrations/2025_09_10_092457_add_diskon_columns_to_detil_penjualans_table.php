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
        Schema::table('detil_penjualans', function (Blueprint $table) {
            $table->unsignedInteger('diskon_nominal')->after('subtotal')->default(0);
            $table->unsignedInteger('subtotal_setelah_diskon')->after('diskon_nominal')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detil_penjualans', function (Blueprint $table) {
            $table->dropColumn(['diskon_nominal', 'subtotal_setelah_diskon']);
        });
    }
};