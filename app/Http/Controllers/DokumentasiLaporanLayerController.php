<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DokumentasiLaporanLayerController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title' => 'Dokumentasi Rumus Laporan Layer',
            'tgl' => $request->tgl,
            'sections' => $this->sections(),
        ];

        return view('laporan.dokumentasi_layer', $data);
    }

    private function sections(): array
    {
        return [
            [
                'title' => 'Data Populasi dan Umur',
                'items' => [
                    [
                        'name' => 'Minggu umur ayam',
                        'formula' => 'pembulatan ke atas(jumlah hari dari chick in sampai tanggal laporan / 7)',
                        'note' => 'Contoh: ayam sudah 50 hari, berarti 50 / 7 = 7,14 lalu dibulatkan menjadi minggu ke-8.',
                    ],
                    [
                        'name' => 'Populasi akhir',
                        'formula' => 'jumlah ayam awal - (total ayam mati + total ayam dijual + total ayam afkir)',
                        'note' => 'Angka ini menunjukkan sisa ayam yang masih ada di kandang.',
                    ],
                    [
                        'name' => 'Persen populasi',
                        'formula' => '(populasi akhir / jumlah ayam awal) * 100',
                        'note' => 'Contoh: sisa 9.000 dari awal 10.000 ayam berarti 90%.',
                    ],
                    [
                        'name' => 'Tanggal afkir',
                        'formula' => 'tanggal chick in + 99 minggu',
                        'note' => 'Jika sudah mendekati 4 minggu sebelum tanggal afkir, tulisan diberi warna merah.',
                    ],
                    [
                        'name' => 'Tanggal chick in 2',
                        'formula' => 'tanggal chick in + 80 minggu',
                        'note' => 'Dipakai sebagai pengingat persiapan chick in berikutnya.',
                    ],
                ],
            ],
            [
                'title' => 'Produksi Telur',
                'items' => [
                    [
                        'name' => 'Kg bersih telur',
                        'formula' => 'kg telur kotor - (butir telur / 180)',
                        'note' => 'Pengurangan ini dipakai untuk mendapatkan berat telur bersih versi laporan.',
                    ],
                    [
                        'name' => 'Gram per butir',
                        'formula' => '(kg bersih telur * 1000) / butir telur',
                        'note' => 'Contoh: 600 kg bersih = 600.000 gram. Jika telur 10.000 butir, rata-ratanya 60 gram per butir.',
                    ],
                    [
                        'name' => 'Butir today - yesterday',
                        'formula' => 'butir telur hari ini - butir telur kemarin',
                        'note' => 'Jika hasilnya turun/minus, laporan memberi warna merah.',
                    ],
                    [
                        'name' => 'Kg today - yesterday',
                        'formula' => 'kg bersih telur hari ini - kg bersih telur kemarin',
                        'note' => 'Dipakai untuk melihat produksi telur naik atau turun dari kemarin.',
                    ],
                    [
                        'name' => 'Persentase grade telur harian',
                        'formula' => '(butir telur grade tertentu / total butir telur hari itu) * 100',
                        'note' => 'Contoh: grade A 2.000 butir dari total 10.000 butir berarti 20%.',
                    ],
                    [
                        'name' => 'Persentase grade kumulatif',
                        'formula' => '(total butir telur grade tertentu / total semua butir telur) * 100',
                        'note' => 'Ini versi akumulasi, bukan hanya produksi satu hari.',
                    ],
                ],
            ],
            [
                'title' => 'HD, HH, dan Performa',
                'items' => [
                    [
                        'name' => 'HD harian',
                        'formula' => '(butir telur hari ini / jumlah ayam yang masih hidup) * 100',
                        'note' => 'HD menunjukkan persentase produksi berdasarkan ayam yang masih hidup.',
                    ],
                    [
                        'name' => 'HD minggu berjalan',
                        'formula' => '(rata-rata butir telur per hari minggu ini / jumlah ayam yang masih hidup) * 100',
                        'note' => 'Misalnya baru berjalan 3 hari, total telur 30.000 butir berarti rata-rata 10.000 butir per hari.',
                    ],
                    [
                        'name' => 'HD minggu lalu',
                        'formula' => '(rata-rata butir telur per hari minggu lalu / jumlah ayam hidup minggu lalu) * 100',
                        'note' => 'Dipakai sebagai pembanding dengan minggu berjalan.',
                    ],
                    [
                        'name' => 'HH harian',
                        'formula' => '(butir telur hari ini / jumlah ayam awal) * 100',
                        'note' => 'HH memakai jumlah ayam awal, bukan ayam hidup saat ini.',
                    ],
                    [
                        'name' => 'HH kumulatif',
                        'formula' => '(total butir telur dari awal sampai tanggal laporan / jumlah ayam awal) * 100',
                        'note' => 'Dipakai untuk melihat hasil produksi sejak awal periode.',
                    ],
                    [
                        'name' => 'Selisih performa HD',
                        'formula' => 'target HD standar - HD aktual kandang',
                        'note' => 'Jika selisihnya lebih dari 3 poin, angka HD diberi warna merah.',
                    ],
                ],
            ],
            [
                'title' => 'Pakan dan FCR',
                'items' => [
                    [
                        'name' => 'Pakan harian kg',
                        'formula' => 'total pakan hari itu dalam gram / 1000',
                        'note' => 'Hasilnya menjadi kilogram pakan.',
                    ],
                    [
                        'name' => 'Gram pakan per ekor per day',
                        'formula' => 'total pakan hari itu dalam gram / jumlah ayam yang masih hidup',
                        'note' => 'Hasilnya menunjukkan rata-rata gram pakan per ekor per hari.',
                    ],
                    [
                        'name' => 'FCR harian',
                        'formula' => 'kg pakan hari itu / kg bersih telur hari itu',
                        'note' => 'Artinya berapa kg pakan yang dibutuhkan untuk menghasilkan 1 kg telur. Jika FCR 2,50 atau lebih, angka diberi warna merah.',
                    ],
                    [
                        'name' => 'FCR harian plus',
                        'formula' => '(kg pakan hari itu + (rupiah vitamin / 7000) + (rupiah vaksin / 7000)) / kg bersih telur hari itu',
                        'note' => 'Nilai vitamin dan vaksin dibagi 7.000 dulu agar menjadi perkiraan setara kg pakan. Jika FCR+ 2,20 atau lebih, angka diberi warna merah.',
                    ],
                    [
                        'name' => 'FCR minggu berjalan',
                        'formula' => 'total kg pakan minggu ini / total kg bersih telur minggu ini',
                        'note' => 'Minggu berjalan dihitung dari hari pertama minggu umur ayam sampai tanggal laporan.',
                    ],
                    [
                        'name' => 'FCR minggu berjalan plus',
                        'formula' => '(total kg pakan minggu ini + (total rupiah vitamin / 7000) + (total rupiah vaksin / 7000)) / total kg bersih telur minggu ini',
                        'note' => 'Versi plus memasukkan biaya tambahan selain pakan.',
                    ],
                    [
                        'name' => 'FCR minggu lalu',
                        'formula' => 'total kg pakan minggu lalu / total kg bersih telur minggu lalu',
                        'note' => 'Dipakai untuk membandingkan FCR minggu ini dengan minggu sebelumnya.',
                    ],
                    [
                        'name' => 'FCR minggu lalu plus',
                        'formula' => '(total kg pakan minggu lalu + (total rupiah vitamin minggu lalu / 7000) + (total rupiah vaksin minggu lalu / 7000)) / total kg bersih telur minggu lalu',
                        'note' => 'Ini versi FCR minggu lalu yang sudah memasukkan biaya tambahan.',
                    ],
                    [
                        'name' => 'FCR kumulatif',
                        'formula' => 'total kg pakan dari awal / total kg bersih telur dari awal',
                        'note' => 'Menunjukkan efisiensi pakan secara keseluruhan sampai tanggal laporan.',
                    ],
                    [
                        'name' => 'FCR kumulatif plus',
                        'formula' => '(total kg pakan dari awal + ((total rupiah vitamin + total rupiah vaksin + nilai ayam) / 7000)) / total kg bersih telur dari awal',
                        'note' => 'Semua nilai rupiah tambahan dibagi 7.000 dulu agar setara dengan kg pakan.',
                    ],
                ],
            ],
            [
                'title' => 'Nilai Rupiah dan PNL',
                'items' => [
                    [
                        'name' => 'Rata-rata harga telur',
                        'formula' => 'total rupiah penjualan telur / total kg telur terjual',
                        'note' => 'Jika penjualan dicatat per butir, jumlah butir diubah ke kg dengan perkiraan 1 butir = 0,06 kg.',
                    ],
                    [
                        'name' => 'R2Rp',
                        'formula' => '(rata-rata harga jual telur harian * produksi telur harian) / total kg bersih',
                        'note' => 'Dipakai untuk memperkirakan nilai rupiah produksi telur.',
                    ],
                    [
                        'name' => 'Biaya pakan',
                        'formula' => 'total rupiah pakan yang dipakai kandang',
                        'note' => 'Yang dihitung hanya produk dengan kategori pakan.',
                    ],
                    [
                        'name' => 'Biaya vitamin',
                        'formula' => 'total rupiah vitamin/obat lewat pakan dan air',
                        'note' => 'Untuk FCR plus, biaya ini dibagi 7.000 agar setara kg pakan.',
                    ],
                    [
                        'name' => 'PNL',
                        'formula' => 'total penjualan telur - total biaya',
                        'note' => 'Jika hasil positif berarti laba, jika negatif berarti rugi.',
                    ],
                    [
                        'name' => 'Rata-rata PNL per kg telur',
                        'formula' => 'PNL / total kg telur',
                        'note' => 'Menunjukkan laba atau rugi rata-rata untuk setiap 1 kg telur.',
                    ],
                ],
            ],
        ];
    }
}
