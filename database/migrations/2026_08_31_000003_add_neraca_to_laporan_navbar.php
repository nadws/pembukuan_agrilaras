<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $navbar = DB::table('navbar')->where('route', 'laporan')->first();
        if (! $navbar) {
            return;
        }

        $routes = $this->parse($navbar->isi);
        $routes[] = 'jurnal-perkiraan.neraca';
        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update([
            'isi' => $this->format(array_values(array_unique($routes))),
        ]);
    }

    public function down(): void
    {
        $navbar = DB::table('navbar')->where('route', 'laporan')->first();
        if (! $navbar) {
            return;
        }

        DB::table('navbar')->where('id_navbar', $navbar->id_navbar)->update([
            'isi' => $this->format(array_values(array_diff($this->parse($navbar->isi), ['jurnal-perkiraan.neraca']))),
        ]);
    }

    private function parse(?string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', str_replace(['[', ']', "'", '"'], '', (string) $value)))));
    }

    private function format(array $routes): string
    {
        return "['" . implode("', '", $routes) . "']";
    }
};
