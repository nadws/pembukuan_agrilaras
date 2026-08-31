<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pembelian_pullet', function (Blueprint $t) {
            $t->id(); $t->string('nomor',40)->unique(); $t->date('tanggal');
            $t->string('nama_pullet'); $t->decimal('qty',14,3)->default(0);
            $t->decimal('total_nilai',16,2)->default(0); $t->unsignedBigInteger('id_akun_proses');
            $t->string('status',20)->default('berjalan'); $t->unsignedBigInteger('id_kandang')->nullable();
            $t->date('tanggal_masuk_kandang')->nullable(); $t->timestamps();
        });
        Schema::create('pembelian_pullet_cicilan', function (Blueprint $t) {
            $t->id(); $t->foreignId('pembelian_pullet_id')->constrained('pembelian_pullet')->cascadeOnDelete();
            $t->date('tanggal'); $t->decimal('nominal',16,2); $t->unsignedBigInteger('id_akun_pembayaran');
            $t->string('nomor_transaksi',40); $t->text('keterangan')->nullable(); $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pembelian_pullet_cicilan'); Schema::dropIfExists('pembelian_pullet'); }
};
