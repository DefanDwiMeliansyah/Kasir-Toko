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
        Schema::table('penjualans', function (Blueprint $table) {
            $table->unsignedBigInteger('diskon_id')->nullable()->after('status');
            $table->unsignedInteger('diskon_nominal')->after('diskon_id')->default(0);
            $table->json('diskon_detail')->nullable()->after('diskon_nominal');
            $table->unsignedInteger('diskon_pelanggan_nominal')->after('diskon_detail')->default(0);
            
            // Add foreign key constraint
            $table->foreign('diskon_id')->references('id')->on('diskons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropForeign(['diskon_id']);
            $table->dropColumn(['diskon_id', 'diskon_nominal', 'diskon_detail', 'diskon_pelanggan_nominal']);
        });
    }
};