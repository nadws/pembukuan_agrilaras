<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('navbar')
            ->where('route', 'laporan_layer')
            ->update([
                'nama' => 'Laporan',
                'route' => 'laporan',
                'isi' => "['laporan', 'laporan_layer', 'laporan_layer.export', 'laporan_layer.hd_tiga_minggu', 'dokumentasi_laporan_layer']",
            ]);
    }

    public function down(): void
    {
        DB::table('navbar')
            ->where('route', 'laporan')
            ->update([
                'nama' => 'Laporan Layer',
                'route' => 'laporan_layer',
                'isi' => "['laporan_layer']",
            ]);
    }
};
