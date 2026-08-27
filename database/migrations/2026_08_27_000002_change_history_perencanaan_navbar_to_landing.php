<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('navbar')) {
            return;
        }

        DB::table('navbar')
            ->where(function ($query) {
                $query->where('route', 'history_perencanaan_pakan')
                    ->orWhereRaw('LOWER(nama) = ?', ['history perencanaan']);
            })
            ->update([
                'route' => 'history_perencanaan',
                'isi' => "['history_perencanaan', 'history_perencanaan_pakan', 'pembukuan_biaya_pv', 'bukukan_pv']",
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('navbar')) {
            return;
        }

        DB::table('navbar')
            ->where('route', 'history_perencanaan')
            ->update([
                'route' => 'history_perencanaan_pakan',
                'isi' => "['history_perencanaan_pakan', 'pembukuan_biaya_pv', 'bukukan_pv']",
            ]);
    }
};
