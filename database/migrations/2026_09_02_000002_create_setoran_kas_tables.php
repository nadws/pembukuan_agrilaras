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
        Schema::create('setoran_kas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_setoran');
            $table->unsignedBigInteger('akun_tujuan_id'); // Akun bank/kas owner
            $table->decimal('nominal_total', 15, 2);
            $table->string('keterangan')->nullable();
            $table->string('nomor_referensi')->nullable();
            $table->timestamps();

            $table->foreign('akun_tujuan_id')->references('id_akun_perkiraan')->on('akun_perkiraan');
        });

        Schema::create('setoran_kas_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('setoran_kas_id');
            $table->unsignedBigInteger('jurnal_perkiraan_id');
            $table->unsignedBigInteger('akun_sumber_id'); // Akun kas penjualan (110108, 110109, 110107)
            $table->decimal('nominal', 15, 2);
            $table->timestamps();

            $table->foreign('setoran_kas_id')->references('id')->on('setoran_kas')->onDelete('cascade');
            $table->foreign('jurnal_perkiraan_id')->references('id_jurnal_perkiraan')->on('jurnal_perkiraan');
            $table->foreign('akun_sumber_id')->references('id_akun_perkiraan')->on('akun_perkiraan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setoran_kas_detail');
        Schema::dropIfExists('setoran_kas');
    }
};
