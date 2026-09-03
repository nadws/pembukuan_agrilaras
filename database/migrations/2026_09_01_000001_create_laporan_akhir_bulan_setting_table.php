<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_akhir_bulan_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->nullable()->index();
            $table->string('kategori', 50)->index(); // 'penarikan', 'penjualan'
            $table->json('tipe_transaksi')->nullable();
            $table->boolean('semua_tipe')->default(false);
            $table->json('akun_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_akhir_bulan_setting');
    }
};
