<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private string $route = 'barang-umum.stok-awal';

    public function up(): void
    {
        $navbar = DB::table('navbar')->where('route', 'data_master')->first();
        if (!$navbar) return;
        $routes = $this->parse($navbar->isi);
        if (!in_array($this->route, $routes, true)) $routes[] = $this->route;
        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update(['isi' => $this->format($routes)]);
    }

    public function down(): void
    {
        $navbar = DB::table('navbar')->where('route', 'data_master')->first();
        if (!$navbar) return;
        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update(['isi' => $this->format(array_values(array_diff($this->parse($navbar->isi), [$this->route]))) ]);
    }

    private function parse(?string $value): array { preg_match_all("/'([^']+)'/", (string) $value, $matches); return array_values(array_filter(array_map('trim', $matches[1] ?? []))); }
    private function format(array $routes): string { return "['" . implode("', '", $routes) . "']"; }
};
