<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembelian_pullet', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('nama_pullet');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_pullet', fn (Blueprint $table) => $table->dropColumn('keterangan'));
    }
};
