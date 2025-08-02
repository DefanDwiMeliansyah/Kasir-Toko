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
            $table->unsignedBigInteger('diskon_id')->nullable()->after('pajak');
            $table->decimal('diskon_nominal', 10, 2)->default(0)->after('diskon_id');
            
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
            $table->dropColumn(['diskon_id', 'diskon_nominal']);
        });
    }
};