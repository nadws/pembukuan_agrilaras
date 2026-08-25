<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('navbar')->updateOrInsert(
            ['nama' => 'Penjualan Martadah'],
            [
                'urutan' => 8,
                'route' => 'penjualan_martadah',
                'isi' => "['penjualan_martadah', 'penjualan_martadah_telur', 'penjualan_martadah_cek', 'detail_penjualan_mtd', 'terima_invoice_mtd', 'save_terima_invoice']",
            ]
        );
    }

    public function down(): void
    {
        DB::table('navbar')
            ->where('nama', 'Penjualan Martadah')
            ->where('route', 'penjualan_martadah')
            ->delete();
    }
};
