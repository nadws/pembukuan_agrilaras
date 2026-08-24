<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('aktiva_pembukuan_baru', function (Blueprint $table) {
        $table->unsignedInteger('sisa_umur_bulan')->nullable()->after('biaya_depresiasi');
        $table->decimal('akumulasi_penyusutan', 18, 2)->default(0)->after('sisa_umur_bulan');
    }); }
    public function down(): void { Schema::table('aktiva_pembukuan_baru', function (Blueprint $table) {
        $table->dropColumn(['sisa_umur_bulan', 'akumulasi_penyusutan']);
    }); }
};
