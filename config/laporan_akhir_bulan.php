<?php

/*
|--------------------------------------------------------------------------
| Pengecualian Laporan Akhir Bulan (bisa diedit tanpa ubah database)
|--------------------------------------------------------------------------
| Setiap pola memakai sintaks LIKE SQL (% = awalan/akhiran bebas).
| Contoh: '%tagihan%' membuang semua deskripsi yang mengandung kata
| "tagihan", sedangkan 'Pembayaran Hutang%' hanya membuang deskripsi
| yang DIAWALI teks tersebut. Keterangan di bawah tampil di halaman
| laporan agar jelas apa saja yang sedang dikecualikan.
*/
return [

    'penarikan_deskripsi_kecuali' => [
        ['pola' => '%setoran%', 'keterangan' => 'Setoran kas/bank penjualan bukan penarikan uang'],
    ],

    'penjualan_deskripsi_kecuali' => [
        ['pola' => '%biaya transportasi%', 'keterangan' => 'Biaya transportasi tidak dihitung sebagai uang penjualan'],
        ['pola' => 'Pembayaran Hutang%', 'keterangan' => 'Pembayaran utang bukan uang penjualan'],
    ],

    'bank_cost_deskripsi_kecuali' => [
        ['pola' => '%transfer%', 'keterangan' => 'Mutasi transfer antar kas/bank bukan biaya'],
        ['pola' => '%penerimaan%', 'keterangan' => 'Penerimaan kas/bank bukan biaya'],
        ['pola' => '%saldo%', 'keterangan' => 'Saldo awal bukan biaya berjalan'],
        ['pola' => '%setoran%', 'keterangan' => 'Setoran tidak dihitung sebagai biaya'],
    ],

    'bank_project_deskripsi_kecuali' => [
        ['pola' => '%pemindahan%', 'keterangan' => 'Pemindahan antar akun bukan realisasi proyek'],
        ['pola' => '%saldo%', 'keterangan' => 'Saldo awal bukan realisasi proyek'],
    ],

];
