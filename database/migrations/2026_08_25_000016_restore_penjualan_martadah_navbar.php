<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private string $routes = "['penjualan_martadah', 'penjualan_martadah_telur', 'penjualan_martadah_cek', 'detail_penjualan_mtd', 'terima_invoice_mtd', 'tbh_pembayaran_martadah', 'save_terima_invoice', 'penjualan_ayam.index', 'penjualan_ayam.cek', 'penjualan_ayam.save_cek', 'penjualan_ayam.penyetoran', 'penjualan_ayam.get_history_perencanaan', 'penjualan_ayam.print_setoran', 'penjualan_ayam.delete_perencanaan', 'penjualan_ayam.perencanaan_setor', 'penjualan_ayam.get_list_perencanaan', 'penjualan_ayam.get_perencanaan', 'penjualan_ayam.save_perencanaan', 'penjualan_ayam.save_setoran']";

    public function up(): void
    {
        DB::table('navbar')->updateOrInsert(
            ['route' => 'penjualan_martadah'],
            ['nama' => 'Penjualan Martadah', 'urutan' => 8, 'isi' => $this->routes]
        );
    }

    public function down(): void
    {
        DB::table('navbar')->where('route', 'penjualan_martadah')->delete();
    }
};
