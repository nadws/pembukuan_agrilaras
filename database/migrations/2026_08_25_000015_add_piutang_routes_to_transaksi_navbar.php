<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $routes = [
        'transaksi.piutang.index',
        'transaksi.piutang.import-accurate',
        'transaksi.piutang.import-accurate-ayam',
        'transaksi.piutang.pelunasan',
        'transaksi.piutang.pelunasan.store',
    ];

    public function up(): void
    {
        $navbar = DB::table('navbar')->where('route', 'transaksi')->first();
        if (!$navbar) return;

        $routes = array_values(array_unique(array_merge($this->parse($navbar->isi), $this->routes)));
        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update([
            'isi' => $this->format($routes),
        ]);
    }

    public function down(): void
    {
        $navbar = DB::table('navbar')->where('route', 'transaksi')->first();
        if (!$navbar) return;

        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update([
            'isi' => $this->format(array_values(array_diff($this->parse($navbar->isi), $this->routes))),
        ]);
    }

    private function parse(?string $value): array
    {
        preg_match_all("/'([^']+)'/", (string) $value, $matches);
        return array_values(array_filter(array_map('trim', $matches[1] ?? [])));
    }

    private function format(array $routes): string
    {
        return "['" . implode("', '", $routes) . "']";
    }
};
