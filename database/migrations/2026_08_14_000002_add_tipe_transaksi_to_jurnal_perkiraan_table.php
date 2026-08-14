<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_perkiraan', function (Blueprint $table) {
            $table->string('tipe_transaksi', 100)->nullable()->after('nomor_transaksi');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_perkiraan', function (Blueprint $table) {
            $table->dropColumn('tipe_transaksi');
        });
    }
};
