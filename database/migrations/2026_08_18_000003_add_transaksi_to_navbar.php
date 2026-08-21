<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('navbar')->updateOrInsert(
            ['route' => 'transaksi'],
            [
                'urutan' => 5,
                'nama' => 'Transaksi',
                'isi' => "['transaksi', 'transaksi.faktur-pembelian.index']",
            ]
        );
    }

    public function down(): void
    {
        DB::table('navbar')->where('route', 'transaksi')->delete();
    }
};
