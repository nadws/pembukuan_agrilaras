<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { if (!Schema::hasColumn('faktur_pembelian','metode_pembayaran')) Schema::table('faktur_pembelian', fn(Blueprint $table) => $table->string('metode_pembayaran',20)->default('hutang')->after('jenis_faktur')); }
    public function down(): void { if (Schema::hasColumn('faktur_pembelian','metode_pembayaran')) Schema::table('faktur_pembelian', fn(Blueprint $table) => $table->dropColumn('metode_pembayaran')); }
};
