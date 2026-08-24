<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoice_telur MODIFY lokasi ENUM('mtd', 'alpa', 'opname', 'transaksi') NOT NULL");

        DB::table('invoice_telur')
            ->where('no_nota', 'like', 'TP%')
            ->where('lokasi', '')
            ->update(['lokasi' => 'transaksi']);
    }

    public function down(): void
    {
        DB::table('invoice_telur')
            ->where('lokasi', 'transaksi')
            ->update(['lokasi' => 'mtd']);

        DB::statement("ALTER TABLE invoice_telur MODIFY lokasi ENUM('mtd', 'alpa', 'opname') NOT NULL");
    }
};
