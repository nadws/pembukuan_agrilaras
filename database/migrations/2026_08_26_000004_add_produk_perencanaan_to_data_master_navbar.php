<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $routes = [
        'produk-perencanaan.index',
        'produk-perencanaan.store',
        'produk-perencanaan.update',
        'produk-perencanaan.destroy',
    ];

    public function up(): void
    {
        $navbar = DB::table('navbar')->where('route', 'data_master')->first();
        if (! $navbar) return;

        preg_match_all("/'([^']+)'/", (string) $navbar->isi, $matches);
        $routes = array_values(array_unique(array_merge($matches[1] ?? [], $this->routes)));

        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)
            ->update(['isi' => "['" . implode("', '", $routes) . "']"]);
    }

    public function down(): void
    {
        $navbar = DB::table('navbar')->where('route', 'data_master')->first();
        if (! $navbar) return;

        preg_match_all("/'([^']+)'/", (string) $navbar->isi, $matches);
        $routes = array_values(array_diff($matches[1] ?? [], $this->routes));

        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)
            ->update(['isi' => "['" . implode("', '", $routes) . "']"]);
    }
};
