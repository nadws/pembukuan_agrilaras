<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gudang_opname_perencanaan')) {
            Schema::create('gudang_opname_perencanaan', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_opname', 100)->unique();
                $table->date('tanggal')->index();
                $table->string('admin', 100);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('gudang_opname_perencanaan_detail')) {
            Schema::create('gudang_opname_perencanaan_detail', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('opname_id');
                $table->integer('id_produk')->index();
                $table->decimal('stok_sistem', 20, 4);
                $table->decimal('stok_fisik', 20, 4);
                $table->decimal('selisih', 20, 4);
                $table->decimal('harga_satuan', 20, 6)->default(0);
                $table->decimal('nilai_selisih', 20, 2)->default(0);
                $table->timestamps();
                $table->foreign('opname_id')->references('id')->on('gudang_opname_perencanaan')->cascadeOnDelete();
                $table->unique(['opname_id', 'id_produk']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gudang_opname_perencanaan_detail');
        Schema::dropIfExists('gudang_opname_perencanaan');
    }
};
