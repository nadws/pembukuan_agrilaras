<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'DB: ' . config('database.connections.' . config('database.default') . '.database') . PHP_EOL;

$tables = DB::select('SHOW TABLES');
echo 'jumlah tabel: ' . count($tables) . PHP_EOL;

$kolom = array_values((array) reset($tables))[0] ?? null;
if ($kolom) {
    echo 'daftar tabel:' . PHP_EOL;
    foreach ($tables as $t) {
        $nama = (array) $t;
        $nama = reset($nama);
        $row = DB::selectOne("SELECT COUNT(*) AS jml FROM `$nama`");
        echo sprintf('  %-40s %s', $nama, number_format((int) $row->jml)) . PHP_EOL;
    }
}

$migrasi = DB::table('migrations')->count();
echo 'jumlah record migration: ' . $migrasi . PHP_EOL;
