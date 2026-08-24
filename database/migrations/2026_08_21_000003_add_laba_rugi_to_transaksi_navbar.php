<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('navbar')
            ->where('route', 'transaksi')
            ->update([
                'isi' => "['transaksi', 'transaksi.faktur-pembelian.index', 'transaksi.faktur-pembelian.create', 'transaksi.faktur-pembelian.detail', 'transaksi.faktur-pembelian.edit', 'transaksi.penerimaan.index', 'transaksi.penerimaan.terima', 'transaksi.buku-hutang.index', 'transaksi.buku-hutang.pelunasan', 'jurnal-perkiraan.laba-rugi', 'jurnal-perkiraan.laba-rugi.export', 'jurnal-perkiraan.detail-akun']",
            ]);
    }

    public function down(): void
    {
        DB::table('navbar')
            ->where('route', 'transaksi')
            ->update([
                'isi' => "['transaksi', 'transaksi.faktur-pembelian.index']",
            ]);
    }
};
