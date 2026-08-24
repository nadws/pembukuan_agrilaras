-- Memperbaiki HTTP 500 pada:
--   /transaksi/piutang?jenis=ayam
--   /transaksi/piutang?jenis=umum (termasuk penjualan pupuk)
--
-- Aman dijalankan berulang kali. Kolom hanya ditambahkan jika belum tersedia.

SET @schema_name = DATABASE();

SET @sql_invoice_ayam = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @schema_name
          AND TABLE_NAME = 'invoice_ayam'
          AND COLUMN_NAME = 'status'
    ),
    'SELECT ''Kolom invoice_ayam.status sudah ada'' AS informasi',
    'ALTER TABLE `invoice_ayam` ADD COLUMN `status` ENUM(''paid'', ''unpaid'') NOT NULL DEFAULT ''paid'' AFTER `lokasi`'
);
PREPARE stmt_invoice_ayam FROM @sql_invoice_ayam;
EXECUTE stmt_invoice_ayam;
DEALLOCATE PREPARE stmt_invoice_ayam;

SET @sql_penjualan_agl = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @schema_name
          AND TABLE_NAME = 'penjualan_agl'
          AND COLUMN_NAME = 'status'
    ),
    'SELECT ''Kolom penjualan_agl.status sudah ada'' AS informasi',
    'ALTER TABLE `penjualan_agl` ADD COLUMN `status` ENUM(''paid'', ''unpaid'') NOT NULL DEFAULT ''paid'' AFTER `lokasi`'
);
PREPARE stmt_penjualan_agl FROM @sql_penjualan_agl;
EXECUTE stmt_penjualan_agl;
DEALLOCATE PREPARE stmt_penjualan_agl;
