<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktiva_gantung_transaksi', function (Blueprint $table) {
            $table->integer('id_akun_kas')->nullable()->change();
            $table->string('sumber', 30)->default('transaksi')->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('aktiva_gantung_transaksi', function (Blueprint $table) {
            $table->dropColumn('sumber');
            $table->integer('id_akun_kas')->nullable(false)->change();
        });
    }
};
