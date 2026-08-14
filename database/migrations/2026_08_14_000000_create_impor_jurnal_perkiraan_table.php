<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impor_jurnal_perkiraan', function (Blueprint $table) {
            $table->bigIncrements('id_impor_jurnal_perkiraan');
            $table->string('nama_file');
            $table->char('hash_file', 64)->unique();
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->unsignedInteger('jumlah_transaksi');
            $table->unsignedInteger('jumlah_detail');
            $table->decimal('total_debit', 24, 12);
            $table->decimal('total_kredit', 24, 12);
            $table->string('status', 20)->default('aktif');
            $table->unsignedBigInteger('diimpor_oleh')->nullable();
            $table->timestamp('dibatalkan_pada')->nullable();
            $table->unsignedBigInteger('dibatalkan_oleh')->nullable();
            $table->timestamps();

            $table->index(['status', 'periode_awal', 'periode_akhir'], 'impor_jurnal_status_periode_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impor_jurnal_perkiraan');
    }
};
