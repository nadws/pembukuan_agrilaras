<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('aktiva_pembukuan_baru', function (Blueprint $table) {
            $table->unsignedInteger('umur_aktiva_bulan')->nullable()->after('biaya_depresiasi');
        });

        DB::table('aktiva_pembukuan_baru')->whereNull('umur_aktiva_bulan')
            ->update(['umur_aktiva_bulan' => DB::raw('sisa_umur_bulan')]);
    }

    public function down(): void
    {
        Schema::table('aktiva_pembukuan_baru', function (Blueprint $table) {
            $table->dropColumn('umur_aktiva_bulan');
        });
    }
};
