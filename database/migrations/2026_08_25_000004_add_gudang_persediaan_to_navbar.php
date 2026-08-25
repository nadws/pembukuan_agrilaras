<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('navbar')->updateOrInsert(
            ['nama' => 'Gudang'],
            [
                'urutan' => 3,
                'nama' => 'Gudang',
                'route' => 'gudang_persediaan',
                'isi' => "['gudang_persediaan', 'gudang-persediaan.index', 'gudang-persediaan.opname', 'gudang-persediaan.opname.store', 'gudang-persediaan.riwayat']",
            ]
        );
    }

    public function down(): void
    {
        DB::table('navbar')->where('nama', 'Gudang')->whereIn('route', ['gudang_persediaan', 'gudang-persediaan.index'])->delete();
    }
};
