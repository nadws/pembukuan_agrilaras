<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelunasan_faktur_pembelian', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('faktur_pembelian_id');
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 24, 2);
            $table->unsignedBigInteger('id_akun_kas');
            $table->unsignedBigInteger('id_impor_jurnal_perkiraan')->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->foreign('faktur_pembelian_id', 'pelunasan_faktur_pembelian_faktur_foreign')
                ->references('id')
                ->on('faktur_pembelian')
                ->cascadeOnDelete();
            $table->foreign('id_akun_kas', 'pelunasan_faktur_pembelian_akun_foreign')
                ->references('id_akun_perkiraan')
                ->on('akun_perkiraan');
            $table->foreign('id_impor_jurnal_perkiraan', 'pelunasan_faktur_pembelian_batch_foreign')
                ->references('id_impor_jurnal_perkiraan')
                ->on('impor_jurnal_perkiraan')
                ->nullOnDelete();

            $table->index(['tanggal_bayar', 'faktur_pembelian_id'], 'pelunasan_faktur_tanggal_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelunasan_faktur_pembelian');
    }
};
