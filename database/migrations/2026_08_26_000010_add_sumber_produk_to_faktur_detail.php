<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { if (!Schema::hasColumn('faktur_pembelian_detail','sumber_produk')) Schema::table('faktur_pembelian_detail', fn(Blueprint $table) => $table->string('sumber_produk',30)->default('perencanaan')->after('pakan_id')); }
    public function down(): void { if (Schema::hasColumn('faktur_pembelian_detail','sumber_produk')) Schema::table('faktur_pembelian_detail', fn(Blueprint $table) => $table->dropColumn('sumber_produk')); }
};
