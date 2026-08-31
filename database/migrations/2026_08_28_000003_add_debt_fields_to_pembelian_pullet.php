<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembelian_pullet', function (Blueprint $table) {
            $table->decimal('total_dibayar', 16, 2)->default(0)->after('total_nilai');
            $table->unsignedBigInteger('id_akun_hutang')->nullable()->after('id_akun_proses');
            $table->boolean('skema_hutang')->default(false)->after('id_akun_hutang');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_pullet', function (Blueprint $table) {
            $table->dropColumn(['total_dibayar', 'id_akun_hutang', 'skema_hutang']);
        });
    }
};
