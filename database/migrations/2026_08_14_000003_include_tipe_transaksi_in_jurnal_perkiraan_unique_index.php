<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_perkiraan', function (Blueprint $table) {
            $table->dropUnique('jurnal_perkiraan_detail_unique');
            $table->unique(
                ['id_impor_jurnal_perkiraan', 'tanggal', 'nomor_transaksi', 'tipe_transaksi', 'urutan_detail'],
                'jurnal_perkiraan_detail_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_perkiraan', function (Blueprint $table) {
            $table->dropUnique('jurnal_perkiraan_detail_unique');
            $table->unique(
                ['id_impor_jurnal_perkiraan', 'tanggal', 'nomor_transaksi', 'urutan_detail'],
                'jurnal_perkiraan_detail_unique'
            );
        });
    }
};
