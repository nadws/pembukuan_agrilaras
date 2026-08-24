<?php

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\Schema::create('pelunasan_piutang_penjualan', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('jenis', 20);
            $table->string('no_nota', 100);
            $table->unsignedBigInteger('id_customer');
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 24, 12);
            $table->unsignedBigInteger('id_akun_pembayaran');
            $table->unsignedBigInteger('id_impor_jurnal_perkiraan')->nullable();
            $table->timestamps();
            $table->index(['jenis', 'no_nota']);
            $table->index(['id_customer', 'tanggal_bayar']);
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\Schema::dropIfExists('pelunasan_piutang_penjualan');
    }
};
