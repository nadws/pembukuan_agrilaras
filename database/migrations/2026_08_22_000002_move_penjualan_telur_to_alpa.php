<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('invoice_telur')
            ->where('no_nota', 'like', 'TP%')
            ->where('lokasi', 'transaksi')
            ->update(['lokasi' => 'alpa']);
    }

    public function down(): void
    {
        DB::table('invoice_telur')
            ->where('no_nota', 'like', 'TP%')
            ->where('lokasi', 'alpa')
            ->update(['lokasi' => 'transaksi']);
    }
};
