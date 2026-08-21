<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_perkiraan_stok_perencanaan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_impor_jurnal_perkiraan');
            $table->unsignedInteger('id_stok_telur')->unique();
            $table->char('check_sebelum', 1)->default('T');
            $table->string('cek_admin_sebelum', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_impor_jurnal_perkiraan', 'jp_stok_batch_foreign')
                ->references('id_impor_jurnal_perkiraan')
                ->on('impor_jurnal_perkiraan')
                ->cascadeOnDelete();
            $table->index('id_impor_jurnal_perkiraan', 'jp_stok_batch_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_perkiraan_stok_perencanaan');
    }
};
