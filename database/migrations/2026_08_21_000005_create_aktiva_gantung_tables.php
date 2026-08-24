<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('aktiva_gantung')) {
            Schema::create('aktiva_gantung', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 30)->unique();
                $table->string('nama_aset');
                $table->text('keterangan')->nullable();
                $table->string('status', 20)->default('gantung');
                $table->integer('created_by')->nullable();
                $table->timestamps();

                $table->index(['status', 'nama_aset']);
            });
        }

        if (! Schema::hasTable('aktiva_gantung_transaksi')) {
            Schema::create('aktiva_gantung_transaksi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('aktiva_gantung_id');
                $table->date('tanggal');
                $table->string('nomor_transaksi', 100);
                $table->integer('id_akun_aktiva_gantung');
                $table->integer('id_akun_kas');
                $table->decimal('jumlah', 15, 2);
                $table->string('keterangan')->nullable();
                $table->unsignedBigInteger('id_impor_jurnal_perkiraan')->nullable();
                $table->integer('created_by')->nullable();
                $table->timestamps();

                $table->foreign('aktiva_gantung_id', 'aktiva_gantung_transaksi_aset_foreign')
                    ->references('id')
                    ->on('aktiva_gantung')
                    ->cascadeOnDelete();
                $table->index(['tanggal', 'aktiva_gantung_id'], 'aktiva_gantung_transaksi_tanggal_index');
                $table->index('nomor_transaksi', 'aktiva_gantung_transaksi_nomor_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aktiva_gantung_transaksi');
        Schema::dropIfExists('aktiva_gantung');
    }
};
