<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('navbar')
            ->where('nama', 'Gudang')
            ->whereIn('route', ['gudang-persediaan.index', 'gudang_persediaan'])
            ->update([
                'route' => 'gudang_persediaan',
                'isi' => "['gudang_persediaan', 'gudang-persediaan.index', 'gudang-persediaan.opname', 'gudang-persediaan.opname.store', 'gudang-persediaan.riwayat']",
            ]);
    }

    public function down(): void
    {
        DB::table('navbar')->where('nama', 'Gudang')->where('route', 'gudang_persediaan')->update([
            'route' => 'gudang-persediaan.index',
            'isi' => "['gudang-persediaan.index', 'gudang-persediaan.opname', 'gudang-persediaan.opname.store', 'gudang-persediaan.riwayat']",
        ]);
    }
};
