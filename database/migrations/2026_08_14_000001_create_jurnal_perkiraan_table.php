<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_perkiraan', function (Blueprint $table) {
            $table->bigIncrements('id_jurnal_perkiraan');
            $table->unsignedBigInteger('id_impor_jurnal_perkiraan');
            $table->unsignedBigInteger('id_akun_perkiraan');
            $table->date('tanggal');
            $table->string('nomor_transaksi', 100);
            $table->unsignedInteger('urutan_detail');
            $table->text('deskripsi')->nullable();
            $table->decimal('debit', 24, 12)->default(0);
            $table->decimal('kredit', 24, 12)->default(0);
            $table->timestamps();

            $table->foreign('id_impor_jurnal_perkiraan', 'jurnal_perkiraan_impor_foreign')
                ->references('id_impor_jurnal_perkiraan')->on('impor_jurnal_perkiraan');
            $table->foreign('id_akun_perkiraan', 'jurnal_perkiraan_akun_foreign')
                ->references('id_akun_perkiraan')->on('akun_perkiraan');
            $table->unique(
                ['id_impor_jurnal_perkiraan', 'tanggal', 'nomor_transaksi', 'urutan_detail'],
                'jurnal_perkiraan_detail_unique'
            );
            $table->index(['tanggal', 'id_akun_perkiraan'], 'jurnal_perkiraan_tanggal_akun_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_perkiraan');
    }
};
