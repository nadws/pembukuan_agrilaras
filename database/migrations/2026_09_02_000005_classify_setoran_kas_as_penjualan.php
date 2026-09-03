<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jurnal_perkiraan')) {
            return;
        }

        DB::table('jurnal_perkiraan')
            ->where('tipe_transaksi', 'KM')
            ->where('nomor_transaksi', 'like', 'SK-%')
            ->update(['tipe_transaksi' => 'Setoran Kas Penjualan']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('jurnal_perkiraan')) {
            return;
        }

        DB::table('jurnal_perkiraan')
            ->where('tipe_transaksi', 'Setoran Kas Penjualan')
            ->where('nomor_transaksi', 'like', 'SK-%')
            ->update(['tipe_transaksi' => 'KM']);
    }
};
