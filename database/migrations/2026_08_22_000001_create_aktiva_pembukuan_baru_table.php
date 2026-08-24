<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aktiva_pembukuan_baru', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_kelompok')->nullable();
            $table->foreign('id_kelompok')->references('id_kelompok')->on('kelompok_aktiva')->nullOnDelete();
            $table->string('nm_aktiva');
            $table->date('tgl');
            $table->decimal('h_perolehan', 18, 2);
            $table->decimal('biaya_depresiasi', 18, 2)->default(0);
            $table->string('admin')->nullable();
            $table->string('sumber')->default('manual');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktiva_pembukuan_baru');
    }
};
