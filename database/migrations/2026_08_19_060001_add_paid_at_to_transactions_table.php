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
        Schema::table('transactions', function (Blueprint $table) {
            // Kapan denda transaksi ini benar-benar dibayar/lunas — TERPISAH dari
            // 'returned_at' (kapan barang fisik kembali) dan status 'completed'
            // (barang sudah diproses admin). Null = belum dibayar (atau memang
            // tidak ada denda). Diisi via aksi admin "Tandai Lunas", bukan
            // otomatis saat 'Tandai Selesai'.
            $table->timestamp('paid_at')->nullable()->after('total_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
};
