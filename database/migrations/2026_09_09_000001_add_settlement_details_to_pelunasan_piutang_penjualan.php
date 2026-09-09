<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelunasan_piutang_penjualan', function (Blueprint $table) {
            $table->decimal('nilai_piutang_dilunasi', 24, 12)->nullable()->after('jumlah_bayar');
            $table->string('jenis_selisih', 10)->default('tidak')->after('nilai_piutang_dilunasi');
            $table->decimal('selisih_pembayaran', 24, 12)->default(0)->after('jenis_selisih');
        });
    }

    public function down(): void
    {
        Schema::table('pelunasan_piutang_penjualan', function (Blueprint $table) {
            $table->dropColumn(['nilai_piutang_dilunasi', 'jenis_selisih', 'selisih_pembayaran']);
        });
    }
};
