<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $navbar = DB::table('navbar')->where('route', 'gudang_persediaan')->first();
        if (! $navbar) {
            return;
        }

        preg_match_all("/'([^']+)'/", (string) $navbar->isi, $matches);
        $routes = array_values(array_unique($matches[1] ?? []));
        foreach (['gudang-persediaan.telur.opname', 'gudang-persediaan.telur.opname.store'] as $route) {
            if (! in_array($route, $routes, true)) {
                $routes[] = $route;
            }
        }
        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update(['isi' => "['" . implode("', '", $routes) . "']"]);
    }

    public function down(): void
    {
        $navbar = DB::table('navbar')->where('route', 'gudang_persediaan')->first();
        if (! $navbar) {
            return;
        }
        $remove = ['gudang-persediaan.telur.opname', 'gudang-persediaan.telur.opname.store'];
        preg_match_all("/'([^']+)'/", (string) $navbar->isi, $matches);
        $routes = array_values(array_filter($matches[1] ?? [], fn ($route) => ! in_array($route, $remove, true)));
        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update(['isi' => "['" . implode("', '", $routes) . "']"]);
    }
};
