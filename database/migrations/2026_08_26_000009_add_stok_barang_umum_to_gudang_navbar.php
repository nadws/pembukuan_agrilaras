<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $route = 'gudang-persediaan.barang-umum';

    public function up(): void
    {
        $navbar = DB::table('navbar')->where('route', 'gudang_persediaan')->first();
        if (! $navbar) return;
        preg_match_all("/'([^']+)'/", (string) $navbar->isi, $matches);
        $routes = array_values(array_unique(array_merge($matches[1] ?? [], [$this->route])));
        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update(['isi' => "['" . implode("', '", $routes) . "']"]);
    }

    public function down(): void
    {
        $navbar = DB::table('navbar')->where('route', 'gudang_persediaan')->first();
        if (! $navbar) return;
        preg_match_all("/'([^']+)'/", (string) $navbar->isi, $matches);
        $routes = array_values(array_diff($matches[1] ?? [], [$this->route]));
        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update(['isi' => "['" . implode("', '", $routes) . "']"]);
    }
};
