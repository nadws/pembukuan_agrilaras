<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('navbar')->updateOrInsert(
            ['route' => 'history_perencanaan_pakan'],
            [
                'urutan' => 7,
                'nama' => 'History Perencanaan',
                'isi' => "['history_perencanaan_pakan', 'pembukuan_biaya_pv', 'bukukan_pv']",
            ]
        );
    }

    public function down(): void
    {
        DB::table('navbar')
            ->where('route', 'history_perencanaan_pakan')
            ->delete();
    }
};
