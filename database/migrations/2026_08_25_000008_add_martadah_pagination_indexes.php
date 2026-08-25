<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_telur', function (Blueprint $table) {
            $table->index(['lokasi', 'tgl', 'no_nota'], 'invoice_telur_lokasi_tgl_nota_idx');
            $table->index(['no_nota', 'id_produk'], 'invoice_telur_nota_produk_idx');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_telur', function (Blueprint $table) {
            $table->dropIndex('invoice_telur_lokasi_tgl_nota_idx');
            $table->dropIndex('invoice_telur_nota_produk_idx');
        });
    }
};
