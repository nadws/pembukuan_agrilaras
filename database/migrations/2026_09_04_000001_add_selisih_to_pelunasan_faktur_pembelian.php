<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pelunasan_faktur_pembelian', function (Blueprint $table) {
            $table->decimal('hutang_dilunasi', 24, 2)->nullable();
            $table->decimal('selisih_biaya', 24, 2)->default(0);
            $table->unsignedBigInteger('id_akun_selisih')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pelunasan_faktur_pembelian', function (Blueprint $table) {
            $table->dropColumn(['hutang_dilunasi', 'selisih_biaya', 'id_akun_selisih']);
        });
    }
};
