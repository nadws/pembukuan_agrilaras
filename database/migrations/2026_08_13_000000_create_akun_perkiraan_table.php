<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun_perkiraan', function (Blueprint $table) {
            $table->bigIncrements('id_akun_perkiraan');
            $table->string('tipe_akun', 20);
            $table->string('kode_perkiraan', 50)->unique();
            $table->string('nama');
            $table->unsignedBigInteger('id_akun_induk')->nullable();
            $table->string('cabang_saldo')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->foreign('id_akun_induk')
                ->references('id_akun_perkiraan')
                ->on('akun_perkiraan')
                ->nullOnDelete();
            $table->index(['tipe_akun', 'aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akun_perkiraan');
    }
};
