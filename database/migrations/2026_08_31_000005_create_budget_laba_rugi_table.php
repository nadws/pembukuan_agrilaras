<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('budget_laba_rugi')) {
            return;
        }

        Schema::create('budget_laba_rugi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_akun_perkiraan');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->decimal('nominal', 20, 2)->default(0);
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();
            $table->unique(['id_akun_perkiraan', 'tahun', 'bulan'], 'budget_lr_akun_periode_unique');
            $table->index(['tahun', 'bulan'], 'budget_lr_periode_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_laba_rugi');
    }
};
