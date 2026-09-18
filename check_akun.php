<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Akun Selisih ===" . PHP_EOL;
$rows = DB::table('akun_perkiraan')->where('aktif', 1)
    ->whereIn('nama', ['Pendapatan Selisih Lebih Bayar', 'Biaya Selisih Kurang Bayar'])
    ->get(['id_akun_perkiraan','kode_perkiraan','nama','tipe_akun']);
foreach ($rows as $r) {
    echo $r->id_akun_perkiraan.' | '.$r->kode_perkiraan.' | '.$r->nama.' | '.$r->tipe_akun.PHP_EOL;
}

echo PHP_EOL . "=== Tipe Akun ===" . PHP_EOL;
echo "Pendapatan -> Laba Rugi (menambah laba)" . PHP_EOL;
echo "Biaya -> Laba Rugi (mengurangi laba)" . PHP_EOL;
echo "Piutang -> Neraca (aset lancar)" . PHP_EOL;
echo "Kas/Bank -> Neraca (aset lancar)" . PHP_EOL;
