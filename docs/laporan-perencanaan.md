# History Perencanaan

Menu **History Perencanaan** yang sudah diaktifkan pengguna menjadi pintu masuk. History Pakan dan History Vitamin & Vaksin tetap memakai `/history_perencanaan_pakan?kategori=...`. Halaman memakai `x-theme.app`, filter tanggal/kandang, tabel, dan pagination. Komponen bersama tidak diubah. Kode kandang dan pembukuan memakai database MySQL yang sama; Tampilkan hanya membaca data tersebut, tanpa impor atau pembukuan ulang.

## Izin dan alur halaman

Tidak ada tabel, migrasi, atau registrasi izin baru. Hak Read memakai izin History Pakan & Vitamin yang sudah ada. Hak mutasi memakai izin lama **Bukukan** (`jenis=create`, tombol 198), kini digunakan untuk Koreksi/Tambah Tertinggal. Role tanpa hak tersebut hanya dapat melihat data/detail. Pengaturan role tetap melalui Role & Access yang sudah ada.

History menampilkan pemakaian baik `check=T` maupun `check=Y`, menggunakan nilai stok yang tersimpan. Filter tanggal/kandang dan kategori membatasi hasil. Tombol **Tampilkan** menjalankan filter; **Koreksi** tiap baris menuju `/history-perencanaan/koreksi?tgl=...&id_kandang=...`. Form pilihan koreksi terpisah dihapus. Identitas terkunci setelah masuk form.

## Export lengkap

**Export Lengkap** memakai tanggal awal/akhir dan kandang pada form filter, termasuk pilihan yang belum ditekan Tampilkan. XLSX berisi Ringkasan per tanggal/kandang, Pakan, Obat Vitamin, Vaksin, Cocokkan Pemakaian, Mutasi Stok, Saldo Produk, dan Jurnal PPH. Karung/pupuk serta kolom ID/flag internal tidak diekspor. Seluruh kategori dan baris diekspor tanpa batas tab/pagination. Laporan memakai nama kandang/produk, judul/periode di tiap sheet, petunjuk singkat, tanggal terbaca, angka/rupiah, baris berselang warna, total rupiah, filter header, freeze pane, serta pengaturan cetak landscape.

Detail memuat komposisi pakan, gram/kg, populasi, gram/ekor, dosis, satuan/campuran, waktu, cara pemakaian, keterangan dan penginput. Vaksin memakai nama, jumlah dan nilai sumber; satuan tidak ditebak karena sumber tidak menyimpannya. Cocokkan Pemakaian membandingkan input pakan/obat pakan-air-per ekor dengan stok keluar per tanggal/kandang/produk, memakai toleransi 0,01 sesuai pengaman koreksi. Input campuran tidak valid atau selisih diberi status Periksa. Vaksin tidak dicocokkan otomatis karena detail sumber tidak memiliki kaitan ID produk stok.

Mutasi Stok mencakup seluruh pergerakan produk yang terkait kandang pilihan, termasuk transaksi kandang lain, karena saldo gudang dipakai bersama. Setiap produk menampilkan saldo awal, debit/masuk, kredit/keluar dan saldo berjalan, diurutkan tanggal dan urutan pencatatan sumber. Saldo Produk merangkum saldo awal/masuk/keluar/akhir serta keluar khusus kandang pilihan. Nama kandang/lokasi tiap mutasi terlihat jelas. Nilai stok/jurnal tetap memakai data tersimpan. Export memakai izin Read History, satu transaksi baca, tanpa tabel/migrasi atau pembukuan ulang. Teks Excel ditulis literal agar keterangan tidak terbaca sebagai formula.

Tombol Cek/Bukukan dan JavaScript seleksi pembukuan dihapus dari History. Endpoint lama `/pembukuan_biaya_pv` dan `/bukukan_pv` menolak akses dengan HTTP 410; jurnal PPH otomatis dari kandang tidak dibukukan kedua kali. Ubin Laporan Perencanaan baru dihapus dari menu Laporan. URL `/laporan/perencanaan` yang dibuat sebelumnya menjadi alias ke History; form lama berbagi izin History yang sama. Aktivasi navbar milik pengguna dipertahankan.

## Aturan simpan

- Tambah hanya untuk tanggal/kandang kosong, termasuk pengecekan obat, karung, vaksin, stok, dan PPH. Duplikasi diarahkan ke Koreksi tanpa menulis data.
- Koreksi memakai identitas terenkripsi, tanggal/kandang terkunci, serta snapshot detail/stok/jurnal/populasi. Data berubah sejak form dibuka menyebabkan penolakan.
- Populasi = stok awal kandang dikurangi mati/jual/afkir sampai tanggal transaksi. Gram pakan = populasi × gram/ekor × persen/100; total persen wajib 100.
- Obat pakan = total pakan kg ÷ campuran × dosis. Obat air memakai dosis total sesuai input kandang; campuran/waktu/cara/keterangan tetap disimpan. Obat ayam = populasi × dosis.
- Nilai stok memakai rata-rata pembelian `total_rp/pcs` sejak 2023-01-01 sampai tanggal transaksi, selain admin `import` dan selain opname. Harga kosong ditolak. Kuantitas/nilai yang melebihi kapasitas penyimpanan ditolak karena strict mode MySQL proyek nonaktif.
- Stock keluar yang diganti dibatasi pada nota detail, kandang/tanggal, produk pakan/obat, dan baris pemakaian biasa. Kecocokan jumlah detail dengan stok lama wajib terverifikasi. Vaksin, pembelian, opname, penyesuaian, kategori lain, dan nota lain dipertahankan. `check=Y` dan `cek_admin` tetap dipertahankan untuk produk lama.
- Saldo stok pada tanggal transaksi dan seluruh tanggal berikutnya diperiksa. Penambahan pemakaian tidak boleh membuat saldo negatif baru atau memperburuk saldo negatif lama.
- Detail, stok, mutasi selisih karung/pupuk, dan jurnal `PPH-YYYYMMDD-IDKANDANG` tipe `Pemakaian Pakan Harian` disimpan dalam satu transaksi. Akun biaya/persediaan aktif wajib tersedia tepat satu. Kegagalan membatalkan seluruh perubahan. Tidak ada JUP.
- Nomor/rute jurnal mengikuti kandang. Transaksi lain dalam batch jurnal bersama dipertahankan dan metadata batch dihitung kembali.

## Karung/pupuk lama dan batas keamanan

Kandang lama menulis `stok_ayam` tanpa nota/kandang: pupuk = total pakan kg × 0,3; kredit karung mengikuti nilai `kg_pakan_box` yang disimpan sebagai `tb_karung_perencanaan.karung`. Rumus/arti kolom lama tersebut dipertahankan, bukan dikonversi diam-diam.

Modul baru menambah **selisih** pada `stok_ayam` dengan nota `PPH-D-...`, tanpa menghapus mutasi lama. Sebelum selisih bukan nol, jumlah stok anonim dan selisih PPH-D pada tanggal tersebut harus cocok dengan total detail semua kandang. Saldo gudang 1 dan saldo tanggal berikutnya juga diperiksa. Pengulangan koreksi dengan jumlah sama tidak menambah mutasi.

Audit salinan pada 8 Oktober 2026 menemukan baseline karung/pupuk tidak cocok untuk **1–7 Oktober 2026**. Koreksi yang mengubah total pakan/karung dan Tambah Tertinggal pada baseline tanggal yang bermasalah ditolak. Perubahan obat tanpa perubahan total pakan/karung tetap dapat diproses jika seluruh pengaman lain lulus. Koreksi jumlah yang tidak dapat direkonsiliasi membutuhkan rekonsiliasi tervalidasi atau migrasi terpisah untuk kaitan nota/kandang; modul tidak menebak pemilik stok anonim.

Seluruh tabel terkait wajib InnoDB. Simpan ditolak jika tabel hilang atau engine tidak mendukung transaksi. Advisory lock mengurutkan simpan modul ini; row lock melindungi detail, sumber baseline harian, stok, populasi, dan jurnal selama transaksi. Penambahan indeks atau unique constraint tidak dilakukan sesuai batas tanpa migrasi.

## Pengujian

Salinan: `agrilaras_perencanaan_test_20261008`. Struktur dan data tabel terkait disalin melalui pembacaan database sumber. Data transaksi uji selalu dibatalkan melalui rollback. Database produksi tidak diubah.

```powershell
$env:PERENCANAAN_TEST_DATABASE = 'agrilaras_perencanaan_test_20261008'
php artisan test --filter=LaporanPerencanaanTest
```

Tes menolak nama database produksi dan hanya menerima nama salinan berawalan `agrilaras_perencanaan_test_`. Tanpa variabel tersebut, tes dilewati. Jangan menjalankan migrasi atau RefreshDatabase pada sumber. Tes meliputi hitung server, obat lengkap, koreksi data kandang salinan, duplikasi, izin endpoint, identitas terkunci, snapshot kedaluwarsa, selisih naik/turun, idempotensi, stok/harga kurang, baseline lama tidak cocok, preservasi vaksin/check/batch jurnal lain, dan rollback saat akun jurnal hilang.

Hasil terbaru: **19 tes lulus, 205 assertion**, termasuk workbook XLSX yang dibaca kembali, export data kandang asli dari salinan, populasi/gram per ekor, detail obat lengkap, judul/header/tanggal/total rupiah, stok melampaui pagination, saldo berjalan dengan transaksi kandang lain, saldo awal/masuk/keluar/akhir, penanda input berbeda dari stok, filter, angka jurnal PPH, literal teks Excel, data kosong, izin export, dan baca tanpa efek samping. History dengan `check=Y` dan penolakan endpoint Bukukan lama tetap teruji. Rute diperiksa pada salinan setelah `route:clear`. Pint dan `git diff --check` lulus. Pemeriksaan visual/interaksi browser belum terverifikasi karena alat browser sebelumnya mengalami timeout; render halaman dan endpoint diverifikasi lewat tes HTTP.
