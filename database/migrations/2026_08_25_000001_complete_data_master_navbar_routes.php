<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $routes = [
        'customer.index',
        'customer.create',
        'customer.update',
        'customer.edit',
        'customer.delete',
        'customer.import',
        'customer.template-import',
        'suplier.create',
        'suplier.update',
        'suplier.edit',
        'suplier.delete',
        'suplier.import',
        'suplier.template-import',
        'produk-perencanaan.index',
        'produk-perencanaan.store',
        'produk-perencanaan.update',
        'produk-perencanaan.destroy',
    ];

    public function up(): void
    {
        $navbar = DB::table('navbar')->where('route', 'data_master')->first();
        if (! $navbar) {
            return;
        }

        $routes = $this->parseRoutes($navbar->isi);
        $routes = array_values(array_unique(array_merge($routes, $this->routes)));

        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update([
            'isi' => $this->formatRoutes($routes),
        ]);
    }

    public function down(): void
    {
        $navbar = DB::table('navbar')->where('route', 'data_master')->first();
        if (! $navbar) {
            return;
        }

        $routes = array_values(array_diff($this->parseRoutes($navbar->isi), $this->routes));
        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update([
            'isi' => $this->formatRoutes($routes),
        ]);
    }

    private function parseRoutes(?string $value): array
    {
        preg_match_all("/'([^']+)'/", (string) $value, $matches);
        return array_values(array_filter(array_map('trim', $matches[1] ?? [])));
    }

    private function formatRoutes(array $routes): string
    {
        return "['" . implode("', '", $routes) . "']";
    }
};
