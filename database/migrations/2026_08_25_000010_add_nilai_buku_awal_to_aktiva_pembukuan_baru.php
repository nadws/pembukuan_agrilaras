<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('aktiva_pembukuan_baru', function (Blueprint $table) {
            $table->decimal('nilai_buku_awal', 18, 2)->nullable()->after('h_perolehan');
        });

        DB::table('aktiva_pembukuan_baru')
            ->whereNull('nilai_buku_awal')
            ->update(['nilai_buku_awal' => DB::raw('GREATEST(0, h_perolehan - akumulasi_penyusutan)')]);
    }

    public function down(): void
    {
        Schema::table('aktiva_pembukuan_baru', function (Blueprint $table) {
            $table->dropColumn('nilai_buku_awal');
        });
    }
};
