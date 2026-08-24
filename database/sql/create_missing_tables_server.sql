-- Tabel yang ada di database lokal tetapi belum ada di server.
-- Dibuat dari hasil SHOW CREATE TABLE database lokal agrilaras_laravel.
-- Skrip ini hanya membuat struktur tabel; data lokal tidak ikut disalin.

SET NAMES utf8mb4;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `aktiva_gantung` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_aset` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gantung',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `aktiva_gantung_kode_unique` (`kode`),
  KEY `aktiva_gantung_status_nama_aset_index` (`status`,`nama_aset`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aktiva_gantung_transaksi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aktiva_gantung_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `nomor_transaksi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_akun_aktiva_gantung` int NOT NULL,
  `id_akun_kas` int NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_impor_jurnal_perkiraan` bigint unsigned DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aktiva_gantung_transaksi_aset_foreign` (`aktiva_gantung_id`),
  KEY `aktiva_gantung_transaksi_tanggal_index` (`tanggal`,`aktiva_gantung_id`),
  KEY `aktiva_gantung_transaksi_nomor_index` (`nomor_transaksi`),
  CONSTRAINT `aktiva_gantung_transaksi_aset_foreign`
    FOREIGN KEY (`aktiva_gantung_id`) REFERENCES `aktiva_gantung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aktiva_pembukuan_baru` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kelompok` int unsigned DEFAULT NULL,
  `id_akun_aset` int unsigned DEFAULT NULL,
  `nm_aktiva` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl` date NOT NULL,
  `h_perolehan` decimal(18,2) NOT NULL,
  `biaya_depresiasi` decimal(18,2) NOT NULL DEFAULT '0.00',
  `sisa_umur_bulan` int unsigned DEFAULT NULL,
  `akumulasi_penyusutan` decimal(18,2) NOT NULL DEFAULT '0.00',
  `admin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sumber` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aktiva_pembukuan_baru_id_kelompok_foreign` (`id_kelompok`),
  CONSTRAINT `aktiva_pembukuan_baru_id_kelompok_foreign`
    FOREIGN KEY (`id_kelompok`) REFERENCES `kelompok_aktiva` (`id_kelompok`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `backup_history_perencanaan_20260817` (
  `id_stok_telur` int unsigned NOT NULL,
  `check_lama` char(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cek_admin_lama` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl` date NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dicadangkan_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_stok_telur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faktur_pembelian` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_faktur` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_faktur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pakan',
  `tanggal_faktur` date NOT NULL,
  `supplier_id` int NOT NULL,
  `jatuh_tempo` date DEFAULT NULL,
  `status_bayar` enum('belum_lunas','lunas','sebagian') COLLATE utf8mb4_unicode_ci DEFAULT 'belum_lunas',
  `total_qty` decimal(12,2) DEFAULT NULL,
  `total_harga` decimal(15,2) DEFAULT NULL,
  `diskon_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_faktur` (`no_faktur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faktur_pembelian_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `faktur_pembelian_id` int NOT NULL,
  `pakan_id` int NOT NULL,
  `qty` decimal(12,2) NOT NULL,
  `satuan` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `no_batch` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_expired` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jurnal_perkiraan_stok_perencanaan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_impor_jurnal_perkiraan` bigint unsigned NOT NULL,
  `id_stok_telur` int unsigned NOT NULL,
  `check_sebelum` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'T',
  `cek_admin_sebelum` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jurnal_perkiraan_stok_perencanaan_id_stok_telur_unique` (`id_stok_telur`),
  KEY `jp_stok_batch_index` (`id_impor_jurnal_perkiraan`),
  CONSTRAINT `jp_stok_batch_foreign`
    FOREIGN KEY (`id_impor_jurnal_perkiraan`)
    REFERENCES `impor_jurnal_perkiraan` (`id_impor_jurnal_perkiraan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pelunasan_faktur_pembelian` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `faktur_pembelian_id` int NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` decimal(24,2) NOT NULL,
  `id_akun_kas` bigint unsigned NOT NULL,
  `id_impor_jurnal_perkiraan` bigint unsigned DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pelunasan_faktur_pembelian_faktur_foreign` (`faktur_pembelian_id`),
  KEY `pelunasan_faktur_pembelian_akun_foreign` (`id_akun_kas`),
  KEY `pelunasan_faktur_pembelian_batch_foreign` (`id_impor_jurnal_perkiraan`),
  KEY `pelunasan_faktur_tanggal_index` (`tanggal_bayar`,`faktur_pembelian_id`),
  CONSTRAINT `pelunasan_faktur_pembelian_akun_foreign`
    FOREIGN KEY (`id_akun_kas`) REFERENCES `akun_perkiraan` (`id_akun_perkiraan`),
  CONSTRAINT `pelunasan_faktur_pembelian_batch_foreign`
    FOREIGN KEY (`id_impor_jurnal_perkiraan`)
    REFERENCES `impor_jurnal_perkiraan` (`id_impor_jurnal_perkiraan`) ON DELETE SET NULL,
  CONSTRAINT `pelunasan_faktur_pembelian_faktur_foreign`
    FOREIGN KEY (`faktur_pembelian_id`) REFERENCES `faktur_pembelian` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pelunasan_piutang_penjualan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_nota` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_customer` bigint unsigned NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` decimal(24,12) NOT NULL,
  `id_akun_pembayaran` bigint unsigned NOT NULL,
  `id_impor_jurnal_perkiraan` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pelunasan_piutang_penjualan_jenis_no_nota_index` (`jenis`,`no_nota`),
  KEY `pelunasan_piutang_penjualan_id_customer_tanggal_bayar_index` (`id_customer`,`tanggal_bayar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pembukuan_baru_stok` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_produk` bigint unsigned NOT NULL,
  `nama_produk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` decimal(18,3) NOT NULL,
  `harga_satuan` decimal(18,2) NOT NULL,
  `tanggal` date NOT NULL,
  `nomor_transaksi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pembukuan_baru_stok_id_produk_index` (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pembukuan_baru_stok_opname` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `id_produk` bigint unsigned NOT NULL,
  `nama_produk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty_sistem` decimal(18,3) NOT NULL,
  `qty_fisik` decimal(18,3) NOT NULL,
  `nilai_selisih` decimal(18,2) NOT NULL,
  `nomor_transaksi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
