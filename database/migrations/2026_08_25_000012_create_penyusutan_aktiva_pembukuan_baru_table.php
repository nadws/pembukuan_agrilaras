<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penyusutan_aktiva_pembukuan_baru', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_aktiva');
            $table->string('periode', 7);
            $table->date('tanggal');
            $table->decimal('nominal', 18, 2);
            $table->string('nomor_transaksi', 50);
            $table->timestamps();

            $table->foreign('id_aktiva')->references('id')->on('aktiva_pembukuan_baru')->cascadeOnDelete();
            $table->unique(['id_aktiva', 'periode'], 'penyusutan_aktiva_periode_unique');
            $table->index(['periode', 'tanggal']);
        });

        DB::statement("UPDATE aktiva_pembukuan_baru
            SET biaya_depresiasi = ROUND(h_perolehan / umur_aktiva_bulan, 2),
                sisa_umur_bulan = CEIL(GREATEST(h_perolehan - akumulasi_penyusutan, 0) / (h_perolehan / umur_aktiva_bulan))
            WHERE umur_aktiva_bulan IS NOT NULL
              AND umur_aktiva_bulan > 0
              AND h_perolehan > 0");
    }

    public function down(): void
    {
        Schema::dropIfExists('penyusutan_aktiva_pembukuan_baru');
    }
};
