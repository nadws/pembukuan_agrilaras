<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jurnal_accurates', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun', 50);
            $table->integer('bulan');
            $table->integer('tahun');
            $table->double('total_biaya')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_accurates');
    }
};
