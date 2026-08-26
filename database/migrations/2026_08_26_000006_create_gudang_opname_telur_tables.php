<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gudang_opname_telur', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_opname')->unique();
            $table->unsignedBigInteger('id_gudang')->index();
            $table->date('tanggal')->index();
            $table->string('admin');
            $table->timestamps();
        });

        Schema::create('gudang_opname_telur_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opname_id')->index();
            $table->unsignedBigInteger('id_telur')->index();
            $table->decimal('stok_sistem_pcs', 18, 4)->default(0);
            $table->decimal('stok_sistem_kg', 18, 4)->default(0);
            $table->decimal('stok_fisik_pcs', 18, 4)->default(0);
            $table->decimal('stok_fisik_kg', 18, 4)->default(0);
            $table->decimal('selisih_pcs', 18, 4)->default(0);
            $table->decimal('selisih_kg', 18, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gudang_opname_telur_detail');
        Schema::dropIfExists('gudang_opname_telur');
    }
};
