<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $labaRugiRoutes = [
        'jurnal-perkiraan.laba-rugi',
        'jurnal-perkiraan.laba-rugi.export',
        'jurnal-perkiraan.detail-akun',
    ];

    public function up(): void
    {
        $this->moveRoutes('akuntansi_baru', 'laporan');
    }

    public function down(): void
    {
        $this->moveRoutes('laporan', 'akuntansi_baru');
    }

    private function moveRoutes(string $fromNavbar, string $toNavbar): void
    {
        $from = DB::table('navbar')->where('route', $fromNavbar)->first();
        $to = DB::table('navbar')->where('route', $toNavbar)->first();

        if ($from) {
            $routes = array_values(array_diff($this->parse($from->isi), $this->labaRugiRoutes));
            DB::table('navbar')->where('id_navbar', $from->id_navbar)->update([
                'isi' => $this->format($routes),
            ]);
        }

        if ($to) {
            $routes = array_values(array_unique(array_merge($this->parse($to->isi), $this->labaRugiRoutes)));
            DB::table('navbar')->where('id_navbar', $to->id_navbar)->update([
                'isi' => $this->format($routes),
            ]);
        }
    }

    private function parse(?string $value): array
    {
        $value = str_replace(['[', ']', "'", '"'], '', (string) $value);

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function format(array $routes): string
    {
        return "['" . implode("', '", $routes) . "']";
    }
};
