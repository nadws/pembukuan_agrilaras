<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->change(true);
    }

    public function down(): void
    {
        $this->change(false);
    }

    private function change(bool $add): void
    {
        $navbar = DB::table('navbar')->where('route', 'laporan')->first();
        if (! $navbar) {
            return;
        }

        $routes = array_values(array_filter(array_map('trim', explode(',', str_replace(['[', ']', "'", '"'], '', (string) $navbar->isi)))));
        $routes = $add
            ? array_values(array_unique(array_merge($routes, ['jurnal-perkiraan.neraca.cetak'])))
            : array_values(array_diff($routes, ['jurnal-perkiraan.neraca.cetak']));

        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update([
            'isi' => "['" . implode("', '", $routes) . "']",
        ]);
    }
};
