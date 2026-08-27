<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('faktur_pembelian_detail')) {
            return;
        }

        if (! Schema::hasColumn('faktur_pembelian_detail', 'id_akun_pembayaran')) {
            Schema::table('faktur_pembelian_detail', function (Blueprint $table) {
                $table->unsignedBigInteger('id_akun_pembayaran')->nullable()->after('subtotal');
                $table->index('id_akun_pembayaran', 'fp_detail_akun_pembayaran_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('faktur_pembelian_detail')
            && Schema::hasColumn('faktur_pembelian_detail', 'id_akun_pembayaran')) {
            Schema::table('faktur_pembelian_detail', function (Blueprint $table) {
                $table->dropIndex('fp_detail_akun_pembayaran_idx');
                $table->dropColumn('id_akun_pembayaran');
            });
        }
    }
};
