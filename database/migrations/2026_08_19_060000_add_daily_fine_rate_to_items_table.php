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
        Schema::table('items', function (Blueprint $table) {
            // Tarif denda per hari keterlambatan, dalam Rupiah, khusus barang ini.
            // Default 0 supaya barang lama (yang belum diisi tarif) tidak
            // mendadak dianggap punya denda saat fitur ini di-deploy.
            $table->unsignedInteger('daily_fine_rate')->default(0)->after('total_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('daily_fine_rate');
        });
    }
};
