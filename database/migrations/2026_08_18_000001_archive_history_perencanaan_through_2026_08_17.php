<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BACKUP_TABLE = 'backup_history_perencanaan_20260817';

    private const ARCHIVE_ADMIN = 'Arsip s.d. 17/08/2026';

    public function up(): void
    {
        Schema::dropIfExists(self::BACKUP_TABLE);

        Schema::create(self::BACKUP_TABLE, function (Blueprint $table) {
            $table->unsignedInteger('id_stok_telur')->primary();
            $table->char('check_lama', 1);
            $table->string('cek_admin_lama', 100)->nullable();
            $table->date('tgl');
            $table->string('kategori', 50);
            $table->timestamp('dicadangkan_pada')->useCurrent();
        });

        DB::statement("
            INSERT INTO `" . self::BACKUP_TABLE . "`
                (`id_stok_telur`, `check_lama`, `cek_admin_lama`, `tgl`, `kategori`)
            SELECT
                s.`id_stok_telur`, s.`check`, s.`cek_admin`, s.`tgl`, p.`kategori`
            FROM `stok_produk_perencanaan` AS s
            INNER JOIN `tb_produk_perencanaan` AS p
                ON p.`id_produk` = s.`id_pakan`
            WHERE s.`check` = 'T'
                AND s.`h_opname` = 'T'
                AND s.`id_kandang` != 0
                AND s.`tgl` <= '2026-08-17'
                AND p.`kategori` IN ('pakan', 'obat_pakan', 'obat_air', 'obat_ayam')
        ");

        DB::statement("
            UPDATE `stok_produk_perencanaan` AS s
            INNER JOIN `" . self::BACKUP_TABLE . "` AS b
                ON b.`id_stok_telur` = s.`id_stok_telur`
            SET s.`check` = 'Y',
                s.`cek_admin` = '" . self::ARCHIVE_ADMIN . "'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE `stok_produk_perencanaan` AS s
            INNER JOIN `" . self::BACKUP_TABLE . "` AS b
                ON b.`id_stok_telur` = s.`id_stok_telur`
            SET s.`check` = b.`check_lama`,
                s.`cek_admin` = b.`cek_admin_lama`
            WHERE s.`cek_admin` = '" . self::ARCHIVE_ADMIN . "'
        ");

        Schema::dropIfExists(self::BACKUP_TABLE);
    }
};
